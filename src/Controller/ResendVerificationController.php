<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResendVerificationFormType;
use App\Service\AppUrlService;
use App\Service\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ResendVerificationController extends AbstractController
{
    #[Route('/resend-verification', name: 'app_resend_verification', methods: ['GET', 'POST'])]
    public function resend(Request $request, EntityManagerInterface $entityManager, EmailVerificationService $emailVerificationService, AppUrlService $appUrlService): Response
    {
        $form = $this->createForm(ResendVerificationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = (string) $form->get('email')->getData();

            // Always show the same message to avoid leaking whether an email exists.
            $this->addFlash('success', 'If your email exists, we will send a new verification link.');

            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($user instanceof User && !$user->isVerified()) {
                $token = $emailVerificationService->generateVerificationToken();
                $user->setVerificationToken($token);
                $entityManager->flush();

                $verificationUrl = $appUrlService->absoluteUrl('app_verify_email', ['token' => $token]);

                try {
                    $emailVerificationService->sendVerificationEmail($user, $verificationUrl);
                } catch (TransportExceptionInterface $e) {
                    $this->addFlash('error', 'We could not send the email right now (SMTP not activated). Please try again later.');
                }
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/resend_verification.html.twig', [
            'resendForm' => $form,
        ]);
    }
}

