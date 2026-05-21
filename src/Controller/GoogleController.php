<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GoogleController extends AbstractController
{
    #[Route('/connect/google', name: 'app_google_connect')]
    public function connect(ClientRegistry $clientRegistry): Response
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect(['openid', 'email', 'profile'], []);
    }

    #[Route('/connect/google/check', name: 'app_google_connect_check')]
    public function connectCheck(): Response
    {
        // This endpoint is handled by `App\Security\GoogleAuthenticator`.
        // If the authenticator can't run for some reason, we return an empty response
        // instead of a fatal exception.
        return new Response('', 204);
    }
}
