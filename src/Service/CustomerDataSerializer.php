<?php

namespace App\Service;

use App\Entity\Appointment;
use App\Entity\Order;
use App\Entity\Payment;
use App\Entity\User;

/** Shared JSON shapes for /api/customer and /api/mobile. */
final class CustomerDataSerializer
{
    /** @return array<string, mixed> */
    public function user(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'isVerified' => $user->isVerified(),
        ];
    }

    /** @return array<string, mixed> */
    public function order(Order $order, bool $detailed = false): array
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
                $data['payments'][] = $this->payment($payment);
            }
        }

        return $data;
    }

    /** @return array<string, mixed> */
    public function payment(Payment $payment): array
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
    public function appointment(Appointment $appointment): array
    {
        return [
            'id' => $appointment->getId(),
            'name' => $appointment->getName(),
            'petType' => $appointment->getPetType(),
            'appointmentDate' => $appointment->getAppointmentDate()?->format(\DateTime::ATOM),
        ];
    }
}
