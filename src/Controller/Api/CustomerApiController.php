<?php

namespace App\Controller\Api;

use App\Entity\Appointment;
use App\Entity\Order;
use App\Entity\User;
use App\Exception\ApiException;
use App\Repository\AppointmentRepository;
use App\Repository\OrderRepository;
use App\Service\ApiResponseFactory;
use App\Service\OrderCheckoutService;
use App\Service\PaymentService;
use App\Validator\PasswordStrength;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Customer-facing REST API (mobile-ready).
 * Isolated from Feature #1 — same contract the mobile app will consume later.
 */
#[Route('/api/customer')]
#[IsGranted('ROLE_USER')]
final class CustomerApiController extends AbstractController
{
    public function __construct(
        private readonly ApiResponseFactory $api,
        private readonly OrderRepository $orderRepository,
        private readonly AppointmentRepository $appointmentRepository,
        private readonly OrderCheckoutService $orderCheckout,
        private readonly PaymentService $paymentService,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('/profile', name: 'api_customer_profile', methods: ['GET'])]
    public function profile(): JsonResponse
    {
        return $this->api->success($this->serializeUser($this->requireUser()));
    }

    #[Route('/profile', name: 'api_customer_profile_update', methods: ['PATCH'])]
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $this->requireUser();
        $payload = $this->decodeJson($request);

        if (isset($payload['email'])) {
            $email = trim((string) $payload['email']);
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->api->error('invalid_email', 'Valid email is required.');
            }
            $user->setEmail($email);
        }

        if (isset($payload['password']) && $payload['password'] !== '') {
            $password = (string) $payload['password'];
            $passwordErrors = $this->validator->validate($password, [new PasswordStrength()]);
            if (\count($passwordErrors) > 0) {
                return $this->api->error('weak_password', (string) $passwordErrors->get(0)->getMessage(), 400);
            }
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        }

        $errors = $this->validator->validate($user);
        if (\count($errors) > 0) {
            throw new \Symfony\Component\Validator\Exception\ValidationFailedException($user, $errors);
        }

        $this->entityManager->flush();

        return $this->api->success($this->serializeUser($user));
    }

    #[Route('/orders', name: 'api_customer_orders', methods: ['GET'])]
    public function orders(): JsonResponse
    {
        $orders = $this->orderRepository->findByUser($this->requireUser());

        return $this->api->success([
            'items' => array_map($this->serializeOrder(...), $orders),
            'count' => \count($orders),
        ]);
    }

    #[Route('/orders/{id}', name: 'api_customer_order_show', methods: ['GET'])]
    public function orderShow(Order $order): JsonResponse
    {
        $this->denyUnlessOwner($order);

        return $this->api->success($this->serializeOrder($order, detailed: true));
    }

    #[Route('/orders', name: 'api_customer_orders_create', methods: ['POST'])]
    public function createOrder(Request $request): JsonResponse
    {
        $payload = $this->decodeJson($request);
        $items = $payload['items'] ?? null;

        if (!\is_array($items) || $items === []) {
            return $this->api->error('invalid_items', 'Provide a non-empty "items" array with productId and quantity.', 422);
        }

        $order = $this->orderCheckout->createOrder($items, $this->requireUser());

        return $this->api->success($this->serializeOrder($order, detailed: true), Response::HTTP_CREATED);
    }

    #[Route('/orders/{id}/payments', name: 'api_customer_order_payment', methods: ['POST'])]
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
            'order' => $this->serializeOrder($order, detailed: true),
            'payment' => $this->serializePayment($payment),
        ], Response::HTTP_CREATED);
    }

    #[Route('/appointments', name: 'api_customer_appointments', methods: ['GET'])]
    public function appointments(): JsonResponse
    {
        $items = $this->appointmentRepository->findByUser($this->requireUser());

        return $this->api->success([
            'items' => array_map($this->serializeAppointment(...), $items),
            'count' => \count($items),
        ]);
    }

    #[Route('/appointments', name: 'api_customer_appointments_create', methods: ['POST'])]
    public function createAppointment(Request $request): JsonResponse
    {
        $payload = $this->decodeJson($request);

        $appointment = new Appointment();
        $appointment->setUser($this->requireUser());
        $appointment->setName(trim((string) ($payload['name'] ?? $payload['Name'] ?? '')));
        $appointment->setPetType((string) ($payload['petType'] ?? $payload['PetType'] ?? ''));

        $dateRaw = $payload['appointmentDate'] ?? $payload['AppointmentDate'] ?? null;
        if (!\is_string($dateRaw) || $dateRaw === '') {
            return $this->api->error('invalid_date', 'appointmentDate is required (ISO 8601).');
        }

        $date = $this->parseAppointmentDate($dateRaw);

        if (!$date instanceof \DateTime) {
            return $this->api->error('invalid_date', 'Could not parse appointmentDate. Use ISO 8601 (e.g. 2026-06-01T10:00:00).');
        }

        $appointment->setAppointmentDate($date);

        $errors = $this->validator->validate($appointment);
        if (\count($errors) > 0) {
            throw new \Symfony\Component\Validator\Exception\ValidationFailedException($appointment, $errors);
        }

        $this->entityManager->persist($appointment);
        $this->entityManager->flush();

        return $this->api->success($this->serializeAppointment($appointment), Response::HTTP_CREATED);
    }

    #[Route('/appointments/{id}', name: 'api_customer_appointment_show', methods: ['GET'])]
    public function appointmentShow(Appointment $appointment): JsonResponse
    {
        $this->denyUnlessAppointmentOwner($appointment);

        return $this->api->success($this->serializeAppointment($appointment));
    }

    private function requireUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new ApiException('unauthorized', 'Authentication required.', 401);
        }

        if (!$user->isVerified()) {
            throw new ApiException('email_not_verified', 'Verify your email before using customer API.', 403);
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

    private function denyUnlessAppointmentOwner(Appointment $appointment): void
    {
        $user = $this->requireUser();
        if ($appointment->getUser()?->getId() !== $user->getId() && !$this->isGranted('ROLE_STAFF')) {
            throw new ApiException('forbidden', 'You do not have access to this appointment.', 403);
        }
    }

    private function parseAppointmentDate(string $dateRaw): ?\DateTime
    {
        try {
            return new \DateTime($dateRaw);
        } catch (\Exception) {
        }

        $formats = [
            \DateTime::ATOM,
            'Y-m-d\TH:i:s',
            'Y-m-d\TH:i:s.u',
            'Y-m-d\TH:i:s.v\Z',
            'Y-m-d H:i:s',
            'Y-m-d\TH:i',
        ];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateRaw);
            if ($date instanceof \DateTime) {
                return $date;
            }
        }

        return null;
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
    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'isVerified' => $user->isVerified(),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeOrder(Order $order, bool $detailed = false): array
    {
        $data = [
            'id' => $order->getId(),
            'status' => $order->getStatus(),
            'subtotal' => $order->getSubtotal(),
            'tax' => $order->getTax(),
            'total' => $order->getTotal(),
            'createdAt' => $order->getCreatedAt()?->format(\DateTime::ATOM),
        ];

        if ($detailed) {
            $data['items'] = [];
            foreach ($order->getItems() as $item) {
                $product = $item->getProduct();
                $data['items'][] = [
                    'id' => $item->getId(),
                    'productId' => $product?->getId(),
                    'productName' => $product?->getName(),
                    'quantity' => $item->getQuantity(),
                    'unitPrice' => $item->getUnitPrice(),
                    'subtotal' => $item->getSubtotal(),
                ];
            }
            $data['payments'] = [];
            foreach ($order->getPayments() as $payment) {
                $data['payments'][] = $this->serializePayment($payment);
            }
        }

        return $data;
    }

    /** @return array<string, mixed> */
    private function serializePayment(\App\Entity\Payment $payment): array
    {
        return [
            'id' => $payment->getId(),
            'amount' => $payment->getAmount(),
            'method' => $payment->getMethod(),
            'status' => $payment->getStatus(),
            'transactionReference' => $payment->getTransactionReference(),
            'createdAt' => $payment->getCreatedAt()?->format(\DateTime::ATOM),
        ];
    }

    /** @return array<string, mixed> */
    private function serializeAppointment(Appointment $appointment): array
    {
        return [
            'id' => $appointment->getId(),
            'name' => $appointment->getName(),
            'petType' => $appointment->getPetType(),
            'appointmentDate' => $appointment->getAppointmentDate()?->format(\DateTime::ATOM),
        ];
    }
}
