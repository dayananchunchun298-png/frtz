<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\User;
use App\Form\AppointmentType;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/appointments')]
final class AppointmentController extends AbstractController
{
    #[Route('/', name: 'app_appointment_index', methods: ['GET'])]
    public function index(AppointmentRepository $appointmentRepository): Response
    {
        $user = $this->getUser();
        if ($this->isGranted('ROLE_STAFF')) {
            $appointments = $appointmentRepository->findAll();
        } elseif ($user instanceof User) {
            $appointments = $appointmentRepository->findByUser($user);
        } else {
            $appointments = [];
        }

        return $this->render('appointment/index.html.twig', [
            'appointments' => $appointments,
        ]);
    }

    #[Route('/new', name: 'app_appointment_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $appointment = new Appointment();
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if ($user instanceof User) {
                $appointment->setUser($user);
            }
            $entityManager->persist($appointment);
            $entityManager->flush();

            $this->addFlash('success', 'Appointment booked successfully!');

            return $this->redirectToRoute('app_appointment_index');
        }

        return $this->render('appointment/new.html.twig', [
            'appointment' => $appointment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_appointment_show', methods: ['GET'])]
    public function show(Appointment $appointment): Response
    {
        $user = $this->getUser();
        if ($user instanceof User
            && !$this->isGranted('ROLE_STAFF')
            && $appointment->getUser()?->getId() !== $user->getId()
        ) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('appointment/show.html.twig', [
            'appointment' => $appointment,
        ]);
    }
}
