<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\User;
use App\Exception\ApiException;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

final class OrderCheckoutService
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param list<array{productId: int, quantity: int}> $lineItems
     */
    public function createOrder(array $lineItems, ?User $user = null): Order
    {
        if ($lineItems === []) {
            throw new ApiException('empty_cart', 'At least one product is required.', 400);
        }

        $order = new Order();
        if ($user instanceof User) {
            $order->setUser($user);
        }

        $subtotal = 0.0;

        foreach ($lineItems as $line) {
            $productId = (int) ($line['productId'] ?? 0);
            $quantity = (int) ($line['quantity'] ?? 0);

            if ($productId < 1 || $quantity < 1) {
                throw new ApiException('invalid_line_item', 'Each line item needs a positive productId and quantity.', 422);
            }

            $product = $this->productRepository->find($productId);
            if (!$product instanceof Product) {
                throw new ApiException('product_not_found', sprintf('Product %d was not found.', $productId), 404);
            }

            if (!$product->isIsActive() || $product->getStock() < 1) {
                throw new ApiException('product_unavailable', sprintf('Product "%s" is not available.', $product->getName()), 400);
            }

            $quantityToBuy = min($quantity, $product->getStock());
            $product->setStock($product->getStock() - $quantityToBuy);

            $lineSubtotal = (float) $product->getPrice() * $quantityToBuy;
            $subtotal += $lineSubtotal;

            $item = new OrderItem();
            $item->setProduct($product);
            $item->setQuantity($quantityToBuy);
            $item->setUnitPrice((string) $product->getPrice());
            $item->setSubtotal((string) $lineSubtotal);
            $order->addItem($item);
        }

        $tax = round($subtotal * 0.08, 2);
        $total = round($subtotal + $tax, 2);

        $order->setSubtotal((string) $subtotal);
        $order->setTax((string) $tax);
        $order->setTotal((string) $total);
        $order->setStatus(Order::STATUS_PENDING);

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order;
    }
}
