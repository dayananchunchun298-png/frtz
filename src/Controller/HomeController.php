<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for handling the homepage and landing page of the Vet Shop system.
 */
final class HomeController extends AbstractController
{
    private const PAGE_TITLE = 'FRTZ PawCare — Healthy Food & Grooming';

    /**
     * Homepage route
     *
     * @return Response
     */
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(): Response
    {
        // Example data for front page (can be expanded later)
        $services = [
            ['name' => 'Dog Food', 'slug' => 'dog-food'],
            ['name' => 'Cat Food', 'slug' => 'cat-food'],
            ['name' => 'Grooming Service', 'slug' => 'grooming'],
        ];

        return $this->render('home/index.html.twig', [
            'title' => self::PAGE_TITLE,
            'services' => $services,
        ]);
    }
}
