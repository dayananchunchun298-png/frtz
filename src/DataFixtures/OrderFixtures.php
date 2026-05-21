<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Exported from local database on 2026-05-22.
 */
class OrderFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    private const GROUP = 'local-export';

    private const ROWS = [
            ['ref' => 'order_1', 'createdAt' => '2025-10-14 10:00:14', 'subtotal' => '57.97', 'tax' => '4.64', 'total' => '62.61', 'status' => 'pending', 'userRef' => null],
            ['ref' => 'order_2', 'createdAt' => '2025-10-14 10:01:31', 'subtotal' => '45.97', 'tax' => '3.68', 'total' => '49.65', 'status' => 'pending', 'userRef' => null],
            ['ref' => 'order_3', 'createdAt' => '2025-10-15 07:45:19', 'subtotal' => '24.99', 'tax' => '2.00', 'total' => '26.99', 'status' => 'pending', 'userRef' => null],
            ['ref' => 'order_4', 'createdAt' => '2025-10-15 07:48:18', 'subtotal' => '45.97', 'tax' => '3.68', 'total' => '49.65', 'status' => 'pending', 'userRef' => null],
            ['ref' => 'order_5', 'createdAt' => '2025-10-15 10:09:51', 'subtotal' => '45.97', 'tax' => '3.68', 'total' => '49.65', 'status' => 'pending', 'userRef' => null],
            ['ref' => 'order_6', 'createdAt' => '2025-10-15 10:28:06', 'subtotal' => '38.98', 'tax' => '3.12', 'total' => '42.10', 'status' => 'pending', 'userRef' => null],
            ['ref' => 'order_7', 'createdAt' => '2025-10-22 18:47:33', 'subtotal' => '32.97', 'tax' => '2.64', 'total' => '35.61', 'status' => 'pending', 'userRef' => null],
            ['ref' => 'order_8', 'createdAt' => '2025-10-22 18:48:00', 'subtotal' => '8.99', 'tax' => '0.72', 'total' => '9.71', 'status' => 'pending', 'userRef' => null],
            ['ref' => 'order_9', 'createdAt' => '2026-05-21 00:20:41', 'subtotal' => '8.99', 'tax' => '0.72', 'total' => '9.71', 'status' => 'pending', 'userRef' => null],
            ['ref' => 'order_10', 'createdAt' => '2026-05-21 01:13:25', 'subtotal' => '24.99', 'tax' => '2.00', 'total' => '26.99', 'status' => 'paid', 'userRef' => 'user_29'],
            ['ref' => 'order_11', 'createdAt' => '2026-05-21 02:49:07', 'subtotal' => '49.98', 'tax' => '4.00', 'total' => '53.98', 'status' => 'pending', 'userRef' => null],
            ['ref' => 'order_12', 'createdAt' => '2026-05-21 18:37:02', 'subtotal' => '8.99', 'tax' => '0.72', 'total' => '9.71', 'status' => 'pending', 'userRef' => null],
    ];

    public static function getGroups(): array
    {
        return [self::GROUP];
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::ROWS as $row) {
            $entity = new Order();
            $entity->setCreatedAt(new \DateTime($row['createdAt']));
                $entity->setSubtotal($row['subtotal']);
                $entity->setTax($row['tax']);
                $entity->setTotal($row['total']);
                $entity->setStatus($row['status']);
                if ($row['userRef'] !== null) {
                    $entity->setUser($this->getReference($row['userRef'], User::class));
                }
            $this->addReference($row['ref'], $entity, Order::class);
            $manager->persist($entity);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            \App\DataFixtures\UserFixtures::class,
        ];
    }
}
