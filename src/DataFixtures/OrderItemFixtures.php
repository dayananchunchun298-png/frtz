<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Exported from local database on 2026-05-22.
 */
class OrderItemFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    private const GROUP = 'local-export';

    private const ROWS = [
            ['ref' => 'order_item_1', 'orderRef' => 'order_1', 'productRef' => 'product_91', 'quantity' => 1, 'unitPrice' => '29.99', 'subtotal' => '29.99'],
            ['ref' => 'order_item_2', 'orderRef' => 'order_1', 'productRef' => 'product_95', 'quantity' => 1, 'unitPrice' => '14.99', 'subtotal' => '14.99'],
            ['ref' => 'order_item_3', 'orderRef' => 'order_1', 'productRef' => 'product_100', 'quantity' => 1, 'unitPrice' => '12.99', 'subtotal' => '12.99'],
            ['ref' => 'order_item_4', 'orderRef' => 'order_2', 'productRef' => 'product_90', 'quantity' => 1, 'unitPrice' => '8.99', 'subtotal' => '8.99'],
            ['ref' => 'order_item_5', 'orderRef' => 'order_2', 'productRef' => 'product_91', 'quantity' => 1, 'unitPrice' => '29.99', 'subtotal' => '29.99'],
            ['ref' => 'order_item_6', 'orderRef' => 'order_2', 'productRef' => 'product_92', 'quantity' => 1, 'unitPrice' => '6.99', 'subtotal' => '6.99'],
            ['ref' => 'order_item_7', 'orderRef' => 'order_3', 'productRef' => 'product_76', 'quantity' => 1, 'unitPrice' => '24.99', 'subtotal' => '24.99'],
            ['ref' => 'order_item_8', 'orderRef' => 'order_4', 'productRef' => 'product_90', 'quantity' => 1, 'unitPrice' => '8.99', 'subtotal' => '8.99'],
            ['ref' => 'order_item_9', 'orderRef' => 'order_4', 'productRef' => 'product_92', 'quantity' => 1, 'unitPrice' => '6.99', 'subtotal' => '6.99'],
            ['ref' => 'order_item_10', 'orderRef' => 'order_4', 'productRef' => 'product_91', 'quantity' => 1, 'unitPrice' => '29.99', 'subtotal' => '29.99'],
            ['ref' => 'order_item_11', 'orderRef' => 'order_5', 'productRef' => 'product_90', 'quantity' => 1, 'unitPrice' => '8.99', 'subtotal' => '8.99'],
            ['ref' => 'order_item_12', 'orderRef' => 'order_5', 'productRef' => 'product_91', 'quantity' => 1, 'unitPrice' => '29.99', 'subtotal' => '29.99'],
            ['ref' => 'order_item_13', 'orderRef' => 'order_5', 'productRef' => 'product_92', 'quantity' => 1, 'unitPrice' => '6.99', 'subtotal' => '6.99'],
            ['ref' => 'order_item_14', 'orderRef' => 'order_6', 'productRef' => 'product_90', 'quantity' => 1, 'unitPrice' => '8.99', 'subtotal' => '8.99'],
            ['ref' => 'order_item_15', 'orderRef' => 'order_6', 'productRef' => 'product_91', 'quantity' => 1, 'unitPrice' => '29.99', 'subtotal' => '29.99'],
            ['ref' => 'order_item_16', 'orderRef' => 'order_7', 'productRef' => 'product_90', 'quantity' => 1, 'unitPrice' => '8.99', 'subtotal' => '8.99'],
            ['ref' => 'order_item_17', 'orderRef' => 'order_7', 'productRef' => 'product_92', 'quantity' => 1, 'unitPrice' => '6.99', 'subtotal' => '6.99'],
            ['ref' => 'order_item_18', 'orderRef' => 'order_7', 'productRef' => 'product_96', 'quantity' => 1, 'unitPrice' => '16.99', 'subtotal' => '16.99'],
            ['ref' => 'order_item_19', 'orderRef' => 'order_8', 'productRef' => 'product_90', 'quantity' => 1, 'unitPrice' => '8.99', 'subtotal' => '8.99'],
            ['ref' => 'order_item_20', 'orderRef' => 'order_9', 'productRef' => 'product_90', 'quantity' => 1, 'unitPrice' => '8.99', 'subtotal' => '8.99'],
            ['ref' => 'order_item_21', 'orderRef' => 'order_10', 'productRef' => 'product_98', 'quantity' => 1, 'unitPrice' => '24.99', 'subtotal' => '24.99'],
            ['ref' => 'order_item_22', 'orderRef' => 'order_11', 'productRef' => 'product_98', 'quantity' => 2, 'unitPrice' => '24.99', 'subtotal' => '49.98'],
            ['ref' => 'order_item_23', 'orderRef' => 'order_12', 'productRef' => 'product_90', 'quantity' => 1, 'unitPrice' => '8.99', 'subtotal' => '8.99'],
    ];

    public static function getGroups(): array
    {
        return [self::GROUP];
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::ROWS as $row) {
            $entity = new OrderItem();
            $order = $this->getReference($row['orderRef'], Order::class);
                $entity->setOrder($order);
                $entity->setProduct($this->getReference($row['productRef'], Product::class));
                $entity->setQuantity($row['quantity']);
                $entity->setUnitPrice($row['unitPrice']);
                $entity->setSubtotal($row['subtotal']);
                $order->addItem($entity);
            $manager->persist($entity);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            \App\DataFixtures\OrderFixtures::class,
            \App\DataFixtures\ProductFixtures::class,
        ];
    }
}
