<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Service\AppUrlService;
use App\Service\EmailVerificationService;
use App\Validator\PasswordStrength;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
final class ApiRegistrationController extends AbstractController
{
    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        EmailVerificationService $emailVerificationService,
        AppUrlService $appUrlService,
        ValidatorInterface $validator,
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload)) {
            return $this->json(['success' => false, 'error' => ['code' => 'invalid_json', 'message' => 'Request body must be JSON.']], Response::HTTP_BAD_REQUEST);
        }

        $email = trim((string) ($payload['email'] ?? ''));
        $password = (string) ($payload['password'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['success' => false, 'error' => ['code' => 'invalid_email', 'message' => 'Valid email is required.']], Response::HTTP_BAD_REQUEST);
        }

        $passwordErrors = $validator->validate($password, [new PasswordStrength()]);
        if (\count($passwordErrors) > 0) {
            return $this->json(['success' => false, 'error' => ['code' => 'weak_password', 'message' => (string) $passwordErrors->get(0)->getMessage()]], Response::HTTP_BAD_REQUEST);
        }

        $existing = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existing instanceof User) {
            return $this->json(['success' => false, 'error' => ['code' => 'email_taken', 'message' => 'Email already registered.']], Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_USER']);
        $user->setIsVerified(false);
        $user->setPassword($passwordHasher->hashPassword($user, $password));
        $token = $emailVerificationService->generateVerificationToken();
        $user->setVerificationToken($token);
        $entityManager->persist($user);
        $entityManager->flush();

        $verificationUrl = $appUrlService->absoluteUrl('app_verify_email', ['token' => $token]);

        $emailSent = true;
        try {
            $emailVerificationService->sendVerificationEmail($user, $verificationUrl);
        } catch (TransportExceptionInterface) {
            $emailSent = false;
        }

        return $this->json([
            'success' => true,
            'data' => [
                'email' => $user->getEmail(),
                'verificationRequired' => true,
                'verificationUrl' => $verificationUrl,
                'emailSent' => $emailSent,
                'message' => $emailSent
                    ? 'Account created. Check your email to verify before logging in.'
                    : 'Account created but email could not be sent. Use resend or web verification.',
            ],
        ], Response::HTTP_CREATED);
    }

    #[Route('/verify-email', name: 'api_verify_email', methods: ['GET'])]
    public function verifyEmail(Request $request, EmailVerificationService $emailVerificationService): JsonResponse
    {
        $token = $request->query->getString('token');
        if ($token === '') {
            return $this->json(['success' => false, 'error' => ['code' => 'missing_token', 'message' => 'Query parameter token is required.']], Response::HTTP_BAD_REQUEST);
        }

        $user = $emailVerificationService->verifyToken($token);
        if (!$user instanceof User) {
            return $this->json(['success' => false, 'error' => ['code' => 'invalid_token', 'message' => 'Invalid or expired token.']], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'success' => true,
            'data' => [
                'email' => $user->getEmail(),
                'verified' => $user->isVerified(),
            ],
        ]);
    }
}
