<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\Payment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Exported from local database on 2026-05-22.
 */
class PaymentFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    private const GROUP = 'local-export';

    private const ROWS = [
            ['ref' => 'payment_1', 'orderRef' => 'order_10', 'amount' => '26.99', 'method' => 'card', 'status' => 'completed', 'transactionReference' => 'PAY-573ED6D1', 'createdAt' => '2026-05-21 01:13:25'],
    ];

    public static function getGroups(): array
    {
        return [self::GROUP];
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::ROWS as $row) {
            $entity = new Payment();
            $order = $this->getReference($row['orderRef'], Order::class);
                $entity->setOrder($order);
                $entity->setAmount($row['amount']);
                $entity->setMethod($row['method']);
                $entity->setStatus($row['status']);
                $entity->setTransactionReference($row['transactionReference']);
                $order->addPayment($entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            \App\DataFixtures\OrderFixtures::class,
        ];
    }
}
