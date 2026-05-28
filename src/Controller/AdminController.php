<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Product;
use App\Entity\Service;
use App\Form\AppointmentType;
use App\Form\ProductType;
use App\Form\ServiceType;
use App\Repository\AppointmentRepository;
use App\Repository\ProductRepository;
use App\Repository\ServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route('', name: 'app_admin')]
    public function index(AppointmentRepository $appointmentRepository, ProductRepository $productRepository, ServiceRepository $serviceRepository): Response
    {
        $canManageAppointments = $this->isGranted('ROLE_ADMIN');

        return $this->render('admin/index.html.twig', [
            'appointments' => $canManageAppointments ? $appointmentRepository->findAll() : [],
            'products' => $productRepository->findAll(),
            'services' => $serviceRepository->findAll(),
            'canManageAppointments' => $canManageAppointments,
        ]);
    }

    #[Route('/appointments/fragment', name: 'app_admin_appointments_fragment', methods: ['GET'])]
    public function appointmentsFragment(AppointmentRepository $appointmentRepository): JsonResponse
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->json([
                'ok' => false,
                'error' => 'forbidden',
            ], Response::HTTP_FORBIDDEN);
        }

        $appointments = $appointmentRepository->findAll();
        $html = $this->renderView('admin/_appointments_grid.html.twig', [
            'appointments' => $appointments,
        ]);

        return $this->json([
            'ok' => true,
            'count' => \count($appointments),
            'html' => $html,
        ]);
    }

    #[Route('/appointment/new', name: 'app_admin_appointment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $appointment = new Appointment();
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($appointment);
            $entityManager->flush();
            
            $this->addFlash('success', 'Appointment for ' . $appointment->getName() . ' has been successfully booked!');

            return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/appointment_new.html.twig', [
            'appointment' => $appointment,
            'form' => $form,
        ]);
    }

    #[Route('/appointment/{id}', name: 'app_admin_appointment_show', methods: ['GET'])]
    public function show(Appointment $appointment): Response
    {
        return $this->render('admin/appointment_show.html.twig', [
            'appointment' => $appointment,
        ]);
    }

    #[Route('/appointment/{id}/edit', name: 'app_admin_appointment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            $this->addFlash('success', 'Appointment for ' . $appointment->getName() . ' has been successfully updated!');

            return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/appointment_edit.html.twig', [
            'appointment' => $appointment,
            'form' => $form,
        ]);
    }

    #[Route('/appointment/{id}', name: 'app_admin_appointment_delete', methods: ['POST'])]
    public function delete(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$appointment->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($appointment);
            $entityManager->flush();
            
            $this->addFlash('success', 'Appointment for ' . $appointment->getName() . ' has been successfully deleted!');
        }

        return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
    }

    // Product CRUD methods
    #[Route('/product/new', name: 'app_admin_product_new', methods: ['GET', 'POST'])]
    public function newProduct(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setUpdatedAt(new \DateTime());
            $entityManager->persist($product);
            $entityManager->flush();
            
            $this->addFlash('success', 'Product "' . $product->getName() . '" has been successfully created!');

            return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/product_new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/product/{id}', name: 'app_admin_product_show', methods: ['GET'])]
    public function showProduct(Product $product): Response
    {
        return $this->render('admin/product_show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/product/{id}/edit', name: 'app_admin_product_edit', methods: ['GET', 'POST'])]
    public function editProduct(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setUpdatedAt(new \DateTime());
            $entityManager->flush();
            
            $this->addFlash('success', 'Product "' . $product->getName() . '" has been successfully updated!');

            return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/product_edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/product/{id}', name: 'app_admin_product_delete', methods: ['POST'])]
    public function deleteProduct(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
            
            $this->addFlash('success', 'Product "' . $product->getName() . '" has been successfully deleted!');
        }

        return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
    }

    // Service CRUD methods
    #[Route('/service/new', name: 'app_admin_service_new', methods: ['GET', 'POST'])]
    public function newService(Request $request, EntityManagerInterface $entityManager): Response
    {
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $service->setUpdatedAt(new \DateTime());
            $entityManager->persist($service);
            $entityManager->flush();
            
            $this->addFlash('success', 'Service "' . $service->getName() . '" has been successfully created!');

            return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/service_new.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/service/{id}', name: 'app_admin_service_show', methods: ['GET'])]
    public function showService(Service $service): Response
    {
        return $this->render('admin/service_show.html.twig', [
            'service' => $service,
        ]);
    }

    #[Route('/service/{id}/edit', name: 'app_admin_service_edit', methods: ['GET', 'POST'])]
    public function editService(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $service->setUpdatedAt(new \DateTime());
            $entityManager->flush();
            
            $this->addFlash('success', 'Service "' . $service->getName() . '" has been successfully updated!');

            return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/service_edit.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/service/{id}', name: 'app_admin_service_delete', methods: ['POST'])]
    public function deleteService(Request $request, Service $service, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$service->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($service);
            $entityManager->flush();
            
            $this->addFlash('success', 'Service "' . $service->getName() . '" has been successfully deleted!');
        }

        return $this->redirectToRoute('app_admin', [], Response::HTTP_SEE_OTHER);
    }
}
