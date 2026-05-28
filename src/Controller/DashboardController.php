<?php

namespace App\Controller;

use App\Repository\AppointmentRepository;
use App\Repository\ProductRepository;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DashboardController extends AbstractController
{
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(Request $request, AppointmentRepository $appointmentRepository, ProductRepository $productRepository, ServiceRepository $serviceRepository): Response
    {
        $allowedSections = ['dashboard', 'appointments', 'products', 'services'];
        $activeSection = $request->query->getString('section', 'dashboard');
        if (!\in_array($activeSection, $allowedSections, true)) {
            $activeSection = 'dashboard';
        }

        return $this->render('admin/index.html.twig', [
            'appointments' => $appointmentRepository->findAll(),
            'products' => $productRepository->findAll(),
            'services' => $serviceRepository->findAll(),
            'canManageAppointments' => true,
            'activeSection' => $activeSection,
        ]);
    }
}

