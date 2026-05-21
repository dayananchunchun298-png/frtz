<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ContactController extends AbstractController
{
    public function __construct(
        private readonly string $contactFormEmbedUrl,
    ) {}

    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $embedUrl = trim($this->contactFormEmbedUrl);

        // Fallback form mode: if no third-party form is configured,
        // allow local submit with clear user feedback.
        if ($embedUrl === '' && $request->isMethod('POST')) {
            $csrf = $request->request->getString('_token');
            if (!$this->isCsrfTokenValid('contact_fallback_form', $csrf)) {
                $this->addFlash('error', 'Invalid contact form token. Please try again.');

                return $this->redirectToRoute('app_contact');
            }

            $name = trim($request->request->getString('name'));
            $email = trim($request->request->getString('email'));
            $message = trim($request->request->getString('message'));

            if ($name === '' || $email === '' || $message === '') {
                $this->addFlash('error', 'Please complete name, email, and message.');

                return $this->redirectToRoute('app_contact');
            }

            $this->addFlash('success', 'Thanks for your message! We received it and will get back to you soon.');

            return $this->redirectToRoute('app_contact');
        }

        return $this->render('contact/index.html.twig', [
            'contactFormEmbedUrl' => $embedUrl,
        ]);
    }
}
