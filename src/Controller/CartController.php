<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Exception\ApiException;
use App\Repository\ProductRepository;
use App\Service\OrderCheckoutService;
use Throwable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart')]
final class CartController extends AbstractController
{
    #[Route('/', name: 'app_cart')]
    public function index(SessionInterface $session, ProductRepository $productRepository): Response
    {
        $cart = $session->get('cart', []);
        $cartItems = [];
        $total = 0;

        foreach ($cart as $productId => $quantity) {
            $product = $productRepository->find($productId);
            if ($product && $product->isIsActive()) {
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $product->getPrice() * $quantity
                ];
                $total += $product->getPrice() * $quantity;
            }
        }

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(Product $product, Request $request, SessionInterface $session): JsonResponse
    {
        if (!$product->isIsActive() || $product->getStock() <= 0) {
            return new JsonResponse(['success' => false, 'message' => 'Product not available'], 400);
        }

        $cart = $session->get('cart', []);
        $productId = $product->getId();

        if (isset($cart[$productId])) {
            $cart[$productId]++;
        } else {
            $cart[$productId] = 1;
        }

        // Check stock limit
        if ($cart[$productId] > $product->getStock()) {
            $cart[$productId] = $product->getStock();
        }

        $session->set('cart', $cart);

        return new JsonResponse([
            'success' => true,
            'message' => 'Product added to cart',
            'cartCount' => array_sum($cart),
            'quantity' => $cart[$productId]
        ]);
    }

    #[Route('/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update(Product $product, Request $request, SessionInterface $session): JsonResponse
    {
        $quantity = (int) $request->request->get('quantity', 1);
        
        if ($quantity <= 0) {
            return $this->remove($product, $session);
        }

        if ($quantity > $product->getStock()) {
            $quantity = $product->getStock();
        }

        $cart = $session->get('cart', []);
        $cart[$product->getId()] = $quantity;
        $session->set('cart', $cart);

        return new JsonResponse([
            'success' => true,
            'message' => 'Cart updated',
            'cartCount' => array_sum($cart),
            'quantity' => $quantity,
            'subtotal' => $product->getPrice() * $quantity
        ]);
    }

    #[Route('/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(Product $product, SessionInterface $session): JsonResponse
    {
        $cart = $session->get('cart', []);
        $productId = $product->getId();

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            $session->set('cart', $cart);
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'Product removed from cart',
            'cartCount' => array_sum($cart)
        ]);
    }

    #[Route('/clear', name: 'app_cart_clear', methods: ['POST'])]
    public function clear(SessionInterface $session): JsonResponse
    {
        $session->remove('cart');

        return new JsonResponse([
            'success' => true,
            'message' => 'Cart cleared',
            'cartCount' => 0
        ]);
    }

    #[Route('/count', name: 'app_cart_count', methods: ['GET'])]
    public function count(SessionInterface $session): JsonResponse
    {
        $cart = $session->get('cart', []);
        $count = array_sum($cart);

        return new JsonResponse(['count' => $count]);
    }

    #[Route('/checkout', name: 'app_cart_checkout', methods: ['POST'])]
    public function checkout(SessionInterface $session, OrderCheckoutService $orderCheckout): JsonResponse
    {
        try {
            $cart = $session->get('cart', []);
            if (empty($cart)) {
                return new JsonResponse(['success' => false, 'message' => 'Your cart is empty'], 400);
            }

            $lineItems = [];
            foreach ($cart as $productId => $quantity) {
                $lineItems[] = ['productId' => (int) $productId, 'quantity' => (int) $quantity];
            }

            $user = $this->getUser();
            $order = $orderCheckout->createOrder($lineItems, $user instanceof User ? $user : null);

            $orderItems = [];
            $total = (float) $order->getSubtotal();
            foreach ($order->getItems() as $item) {
                $product = $item->getProduct();
                $orderItems[] = [
                    'name' => $product?->getName(),
                    'price' => (float) $item->getUnitPrice(),
                    'quantity' => $item->getQuantity(),
                    'subtotal' => (float) $item->getSubtotal(),
                ];
            }

            $orderNumber = 'ORD-' . strtoupper(dechex(time())) . '-' . random_int(100, 999);
            $session->remove('cart');

            $session->set('last_order_summary', [
                'orderNumber' => $orderNumber,
                'orderId' => $order->getId(),
                'items' => $orderItems,
                'total' => $total,
                'tax' => (float) $order->getTax(),
                'grandTotal' => (float) $order->getTotal(),
            ]);

            return new JsonResponse(['success' => true, 'redirect' => $this->generateUrl('app_cart_checkout_success')]);
        } catch (ApiException $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], $e->httpStatus);
        } catch (Throwable $e) {
            return new JsonResponse(['success' => false, 'message' => 'Checkout error: '.$e->getMessage()], 500);
        }
    }

    #[Route('/checkout/success', name: 'app_cart_checkout_success', methods: ['GET'])]
    public function checkoutSuccess(SessionInterface $session): Response
    {
        $summary = $session->get('last_order_summary');
        if (!$summary) {
            return $this->redirectToRoute('app_cart');
        }
        // Remove after reading so refresh doesn't re-show
        $session->remove('last_order_summary');

        return $this->render('cart/checkout_success.html.twig', [
            'summary' => $summary,
        ]);
    }
}
