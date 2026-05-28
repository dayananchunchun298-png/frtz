<?php

namespace App\Controller\Api;

use App\Entity\Appointment;
use App\Entity\Order;
use App\Entity\User;
use App\Exception\ApiException;
use App\Repository\AppointmentRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\ServiceRepository;
use App\Service\ApiResponseFactory;
use App\Service\CustomerDataSerializer;
use App\Service\OrderCheckoutService;
use App\Service\PaymentService;
use App\Service\RealtimeEventBus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Customer mobile API — catalog (public) and account CRUD (JWT).
 * Writes use the same services as /api/customer for web/mobile sync.
 */
#[Route('/api/mobile')]
final class MobileApiController extends AbstractController
{
    public function __construct(
        private readonly ApiResponseFactory $api,
        private readonly CustomerDataSerializer $serializer,
        private readonly OrderCheckoutService $orderCheckout,
        private readonly PaymentService $paymentService,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly RealtimeEventBus $realtime,
    ) {
    }

    #[Route('/health', name: 'api_mobile_health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return $this->api->success([
            'status' => 'ok',
            'app' => 'FRTZ PawCare',
            'version' => '1.0',
        ]);
    }

    #[Route('/products', name: 'api_mobile_products', methods: ['GET'])]
    public function products(ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->findActiveProducts();

        return $this->api->success([
            'items' => array_map($this->serializeProduct(...), $products),
            'count' => \count($products),
        ]);
    }

    #[Route('/services', name: 'api_mobile_services', methods: ['GET'])]
    public function services(ServiceRepository $serviceRepository): JsonResponse
    {
        $services = $serviceRepository->findAll();

        return $this->api->success([
            'items' => array_map($this->serializeService(...), $services),
            'count' => \count($services),
        ]);
    }

    #[Route('/sync/catalog', name: 'api_mobile_sync_catalog', methods: ['GET'])]
    public function syncCatalog(ProductRepository $productRepository, ServiceRepository $serviceRepository): JsonResponse
    {
        $products = $productRepository->findActiveProducts();
        $services = $serviceRepository->findAll();

        return $this->api->success([
            'syncedAt' => (new \DateTime())->format(\DateTime::ATOM),
            'products' => array_map($this->serializeProduct(...), $products),
            'services' => array_map($this->serializeService(...), $services),
        ]);
    }

    /** Full account snapshot for mobile pull-to-refresh (JWT). */
    #[Route('/sync', name: 'api_mobile_sync', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function sync(
        ProductRepository $productRepository,
        ServiceRepository $serviceRepository,
        OrderRepository $orderRepository,
        AppointmentRepository $appointmentRepository,
    ): JsonResponse {
        $user = $this->requireUser();
        $products = $productRepository->findActiveProducts();
        $services = $serviceRepository->findAll();
        $orders = $orderRepository->findByUser($user);
        $appointments = $appointmentRepository->findByUser($user);

        return $this->api->success([
            'syncedAt' => (new \DateTime())->format(\DateTime::ATOM),
            'user' => $this->serializer->user($user),
            'products' => array_map($this->serializeProduct(...), $products),
            'services' => array_map($this->serializeService(...), $services),
            'orders' => [
                'items' => array_map(fn (Order $o) => $this->serializer->order($o), $orders),
                'count' => \count($orders),
            ],
            'appointments' => [
                'items' => array_map(fn (Appointment $a) => $this->serializer->appointment($a), $appointments),
                'count' => \count($appointments),
            ],
        ]);
    }

    #[Route('/me', name: 'api_mobile_me', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function me(): JsonResponse
    {
        return $this->api->success($this->serializer->user($this->requireUser()));
    }

    #[Route('/orders', name: 'api_mobile_orders', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function orders(OrderRepository $orderRepository): JsonResponse
    {
        $orders = $orderRepository->findByUser($this->requireUser());

        return $this->api->success([
            'items' => array_map(fn (Order $o) => $this->serializer->order($o, true), $orders),
            'count' => \count($orders),
        ]);
    }

    #[Route('/orders', name: 'api_mobile_orders_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createOrder(Request $request): JsonResponse
    {
        $payload = $this->decodeJson($request);
        $items = $payload['items'] ?? null;

        if (!\is_array($items) || $items === []) {
            return $this->api->error('invalid_items', 'Provide a non-empty "items" array with productId and quantity.', 422);
        }

        $order = $this->orderCheckout->createOrder($items, $this->requireUser());

        return $this->api->success($this->serializer->order($order, true), Response::HTTP_CREATED);
    }

    #[Route('/orders/{id}/payments', name: 'api_mobile_order_payment', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function payOrder(Order $order, Request $request): JsonResponse
    {
        $this->denyUnlessOwner($order);

        $payload = $this->decodeJson($request);
        $method = trim((string) ($payload['method'] ?? 'card'));
        if ($method === '') {
            $method = 'card';
        }

        $payment = $this->paymentService->processPayment($order, $method);

        return $this->api->success([
            'order' => $this->serializer->order($order, true),
            'payment' => $this->serializer->payment($payment),
        ], Response::HTTP_CREATED);
    }

    #[Route('/appointments', name: 'api_mobile_appointments', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function appointments(AppointmentRepository $appointmentRepository): JsonResponse
    {
        $items = $appointmentRepository->findByUser($this->requireUser());

        return $this->api->success([
            'items' => array_map(fn (Appointment $a) => $this->serializer->appointment($a), $items),
            'count' => \count($items),
        ]);
    }

    #[Route('/appointments', name: 'api_mobile_appointments_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createAppointment(Request $request): JsonResponse
    {
        $payload = $this->decodeJson($request);

        $appointment = new Appointment();
        $appointment->setUser($this->requireUser());
        $appointment->setName(trim((string) ($payload['name'] ?? '')));
        $appointment->setPetType((string) ($payload['petType'] ?? ''));

        $dateRaw = $payload['appointmentDate'] ?? null;
        if (!\is_string($dateRaw) || $dateRaw === '') {
            return $this->api->error('invalid_date', 'appointmentDate is required (ISO 8601).');
        }

        try {
            $date = new \DateTime($dateRaw);
        } catch (\Exception) {
            $date = \DateTime::createFromFormat(\DateTime::ATOM, $dateRaw)
                ?: \DateTime::createFromFormat('Y-m-d\TH:i:s', $dateRaw)
                ?: \DateTime::createFromFormat('Y-m-d H:i:s', $dateRaw)
                ?: \DateTime::createFromFormat('Y-m-d\TH:i', $dateRaw);
        }

        if (!$date instanceof \DateTime) {
            return $this->api->error('invalid_date', 'Could not parse appointmentDate.');
        }

        $appointment->setAppointmentDate($date);

        $errors = $this->validator->validate($appointment);
        if (\count($errors) > 0) {
            throw new \Symfony\Component\Validator\Exception\ValidationFailedException($appointment, $errors);
        }

        $this->entityManager->persist($appointment);
        $this->entityManager->flush();

        $this->realtime->publish('appointment.created', [
            'entity' => 'appointment',
            'id' => $appointment->getId(),
            'data' => $this->serializer->appointment($appointment),
        ]);

        return $this->api->success($this->serializer->appointment($appointment), Response::HTTP_CREATED);
    }

    private function requireUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new ApiException('unauthorized', 'Authentication required.', 401);
        }

        if (!$user->isVerified()) {
            throw new ApiException('email_not_verified', 'Verify your email before using the mobile API.', 403);
        }

        return $user;
    }

    private function denyUnlessOwner(Order $order): void
    {
        $user = $this->requireUser();
        if ($order->getUser()?->getId() !== $user->getId() && !$this->isGranted('ROLE_STAFF')) {
            throw new ApiException('forbidden', 'You do not have access to this order.', 403);
        }
    }

    /** @return array<string, mixed> */
    private function decodeJson(Request $request): array
    {
        $payload = json_decode($request->getContent(), true);
        if (!\is_array($payload)) {
            throw new ApiException('invalid_json', 'Request body must be JSON.', 400);
        }

        return $payload;
    }

    /** @return array<string, mixed> */
    private function serializeProduct(\App\Entity\Product $p): array
    {
        return [
            'id' => $p->getId(),
            'name' => $p->getName(),
            'description' => $p->getDescription(),
            'price' => $p->getPrice(),
            'category' => $p->getCategory(),
            'petType' => $p->getPetType(),
            'stock' => $p->getStock(),
            'image' => $p->getImage(),
            'isActive' => $p->isIsActive(),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeService(\App\Entity\Service $s): array
    {
        return [
            'id' => $s->getId(),
            'name' => $s->getName(),
            'description' => $s->getDescription(),
            'price' => $s->getPrice(),
            'petType' => $s->getPetType(),
            'isActive' => $s->isIsActive(),
        ];
    }
}
