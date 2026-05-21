<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/shop')]
final class ShopController extends AbstractController
{
    #[Route('/', name: 'app_shop')]
    public function index(ProductRepository $productRepository, Request $request): Response
    {
        $category = $request->query->get('category');
        $petType = $request->query->get('pet_type');
        
        if ($category) {
            $products = $productRepository->findByCategory($category);
        } elseif ($petType) {
            $products = $productRepository->findByPetType($petType);
        } else {
            $products = $productRepository->findActiveProducts();
        }

        // Get unique categories and pet types for filters
        $allProducts = $productRepository->findActiveProducts();
        $categories = array_unique(array_map(fn($product) => $product->getCategory(), $allProducts));
        $petTypes = array_unique(array_map(fn($product) => $product->getPetType(), $allProducts));

        return $this->render('shop/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'petTypes' => $petTypes,
            'currentCategory' => $category,
            'currentPetType' => $petType,
        ]);
    }

    #[Route('/product/{id}', name: 'app_shop_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('shop/product_show.html.twig', [
            'product' => $product,
        ]);
    }
}
