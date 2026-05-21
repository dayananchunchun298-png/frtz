<?php

namespace App\Controller;

use App\Repository\AppointmentRepository;
use App\Repository\ProductRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DashboardController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(AppointmentRepository $appointmentRepository, ProductRepository $productRepository, ServiceRepository $serviceRepository): Response
    {
        return $this->render('admin/index.html.twig', [
            'appointments' => $appointmentRepository->findAll(),
            'products' => $productRepository->findAll(),
            'services' => $serviceRepository->findAll(),
            'canManageAppointments' => true,
        ]);
    }
}

