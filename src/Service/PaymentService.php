<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\Payment;
use App\Exception\ApiException;
use Doctrine\ORM\EntityManagerInterface;

final class PaymentService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function processPayment(Order $order, string $method = 'card'): Payment
    {
        if ($order->getStatus() === Order::STATUS_PAID) {
            throw new ApiException('order_already_paid', 'This order has already been paid.', 409);
        }

        if ($order->getStatus() === Order::STATUS_CANCELLED) {
            throw new ApiException('order_cancelled', 'Cannot pay for a cancelled order.', 400);
        }

        $payment = new Payment();
        $payment->setOrder($order);
        $payment->setAmount($order->getTotal() ?? '0.00');
        $payment->setMethod($method);
        $payment->setStatus(Payment::STATUS_COMPLETED);
        $payment->setTransactionReference('PAY-'.strtoupper(bin2hex(random_bytes(4))));

        $order->setStatus(Order::STATUS_PAID);
        $order->addPayment($payment);

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        return $payment;
    }
}
