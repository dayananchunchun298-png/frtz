<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/staff')]
#[IsGranted('ROLE_STAFF')]
final class StaffController extends AbstractController
{
    #[Route('', name: 'app_staff_dashboard')]
    public function dashboard(): Response
    {
        return $this->redirect($this->generateUrl('app_admin').'#products');
    }
}
