<?php

namespace App\Controller\Api;

use App\Service\RealtimeEventBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/realtime')]
final class RealtimeApiController extends AbstractController
{
    #[Route('/events', name: 'api_realtime_events', methods: ['GET'])]
    public function events(Request $request, RealtimeEventBus $bus): JsonResponse
    {
        $since = $request->query->getString('since', '');
        $events = $bus->getSince($since !== '' ? $since : null);

        return $this->json([
            'ok' => true,
            'events' => $events,
            'count' => \count($events),
        ]);
    }
}
