<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\AppUrlService;
use App\Service\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

final class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        EmailVerificationService $emailVerificationService,
        AppUrlService $appUrlService,
    ): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles(['ROLE_USER']);
            $user->setIsVerified(false);

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                (string) $form->get('plainPassword')->getData()
            );
            $user->setPassword($hashedPassword);

            $token = $emailVerificationService->generateVerificationToken();
            $user->setVerificationToken($token);

            $entityManager->persist($user);
            $entityManager->flush();

            $verificationUrl = $appUrlService->absoluteUrl('app_verify_email', ['token' => $token]);
            try {
                $emailVerificationService->sendVerificationEmail($user, $verificationUrl);
                $this->addFlash('success', 'Account created successfully. Please verify your email to log in.');
            } catch (TransportExceptionInterface $e) {
                // Brevo/SMTP is sometimes not activated yet. We still create the account, but login remains blocked.
                $this->addFlash('error', 'Account created, but we could not send the verification email yet. Please try again later.');
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}

