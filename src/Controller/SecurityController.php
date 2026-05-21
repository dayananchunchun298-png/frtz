<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

final class SecurityController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error_message' => $this->resolveLoginErrorMessage($authenticationUtils->getLastAuthenticationError()),
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['POST', 'GET'])]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    private function resolveLoginErrorMessage(?AuthenticationException $error): ?string
    {
        if ($error === null) {
            return null;
        }

        $key = $error->getMessageKey();
        $translated = $this->translator->trans($key, $error->getMessageData(), 'security');

        $friendly = match ($key) {
            'Invalid credentials.', 'Invalid credentials',
            'The presented password is invalid.', 'The presented password is invalid' => 'Wrong email or password. Please try again.',
            default => null,
        };

        if ($friendly !== null) {
            return $friendly;
        }

        if ($translated !== '' && $translated !== $key) {
            return $translated;
        }

        $custom = trim($error->getMessage());
        if ($custom !== '' && $custom !== $key) {
            return $custom;
        }

        return 'We could not sign you in. Please try again.';
    }
}
