<?php

namespace App\Service;

use App\Entity\Product;

/**
 * Picks a product photo URL from the product name, category, and pet type.
 */
final class ProductImageMatcher
{
    private const Q = '?w=400&h=400&fit=crop';

    /** @var array<string, string> */
    private const URLS = [
        'dog_food' => 'https://images.unsplash.com/photo-1589924691995-400dc9ecc119'.self::Q,
        'dog_food_salmon' => 'https://images.unsplash.com/photo-1598463310879-4804de022573'.self::Q,
        'puppy_food' => 'https://images.unsplash.com/photo-1552053831-71594a27632d'.self::Q,
        'dog_treats' => 'https://images.unsplash.com/photo-1615740431354-7b1aef7861cf'.self::Q,
        'cat_food' => 'https://images.unsplash.com/photo-1596854407944-b87cf12b3039'.self::Q,
        'cat_food_wet' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5'.self::Q,
        'kitten_food' => 'https://images.unsplash.com/photo-1529770891173-567f266563d5'.self::Q,
        'cat_treats' => 'https://images.unsplash.com/photo-1493974671818-edad47c10353'.self::Q,
        'dog_toy_puzzle' => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b'.self::Q,
        'rope_toy' => 'https://images.unsplash.com/photo-1583511655857-d58736495a0f'.self::Q,
        'tennis_ball' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64'.self::Q,
        'kong_toy' => 'https://images.unsplash.com/photo-1587300003388-59208cc556f6'.self::Q,
        'catnip_mouse' => 'https://images.unsplash.com/photo-1526336024174-e58f5cdd8e13'.self::Q,
        'feather_wand' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131'.self::Q,
        'scratching_post' => 'https://images.unsplash.com/photo-1545249390-8aa2f7d40342'.self::Q,
        'laser_toy' => 'https://images.unsplash.com/photo-1574158622682-e40e69881006'.self::Q,
        'dog_collar' => 'https://images.unsplash.com/photo-1600275665164-949553a2e0e0'.self::Q,
        'dog_leash' => 'https://images.unsplash.com/photo-1558785078-eca7d0ecf9f0'.self::Q,
        'dog_tag' => 'https://images.unsplash.com/photo-1587300003388-59208cc556f6'.self::Q,
        'dog_harness' => 'https://images.unsplash.com/photo-1583511655826-05700d62f0e2'.self::Q,
        'cat_collar' => 'https://images.unsplash.com/photo-1513245543132-31f507417b26'.self::Q,
        'cat_carrier' => 'https://images.unsplash.com/photo-1544568100-847a948facb9'.self::Q,
        'litter_box' => 'https://images.unsplash.com/photo-1598134493179-51332e56807f'.self::Q,
        'pet_bowls' => 'https://images.unsplash.com/photo-1608846894770-d6d71cae3f56'.self::Q,
        'dental_chews' => 'https://images.unsplash.com/photo-1615740431354-7b1aef7861cf'.self::Q,
        'hairball_remedy' => 'https://images.unsplash.com/photo-1574158622682-e40e69881006'.self::Q,
        'first_aid' => 'https://images.unsplash.com/photo-1576201836106-c179f380e165'.self::Q,
        'flea_tick' => 'https://images.unsplash.com/photo-1587300003388-59208cc556f6'.self::Q,
        'grooming_brush' => 'https://images.unsplash.com/photo-1516734212186-a967f33d1f6d'.self::Q,
        'grooming_glove' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131'.self::Q,
        'pet_shampoo' => 'https://images.unsplash.com/photo-1583337130417-3346a1be7dee'.self::Q,
        'nail_clippers' => 'https://images.unsplash.com/photo-1560807707-8cc77767d783'.self::Q,
        'dog_bed' => 'https://images.unsplash.com/photo-1596495578065-6a8cd91d4787'.self::Q,
        'cat_bed' => 'https://images.unsplash.com/photo-1519052537078-e6302a49648e'.self::Q,
        'pet_blanket' => 'https://images.unsplash.com/photo-1450778862550-6aede4c644fe'.self::Q,
        'dog_toy' => 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1'.self::Q,
        'cat_toy' => 'https://images.unsplash.com/photo-1574158622682-e40e69881006'.self::Q,
        'dog_accessory' => 'https://images.unsplash.com/photo-1587300003388-59208cc556f6'.self::Q,
        'cat_accessory' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131'.self::Q,
        'pet_health' => 'https://images.unsplash.com/photo-1576201836106-c179f380e165'.self::Q,
        'pet_grooming' => 'https://images.unsplash.com/photo-1516734212186-a967f33d1f6d'.self::Q,
        'pet_bedding' => 'https://images.unsplash.com/photo-1596495578065-6a8cd91d4787'.self::Q,
    ];

    public function resolveForProduct(Product $product): string
    {
        return $this->resolve(
            (string) $product->getName(),
            (string) $product->getCategory(),
            (string) $product->getPetType(),
        );
    }

    public function resolve(string $name, string $category, string $petType): string
    {
        $n = strtolower($name);
        $c = strtolower($category);
        $pet = strtolower($petType);

        return match (true) {
            str_contains($n, 'tennis ball') => self::URLS['tennis_ball'],
            str_contains($n, 'rope toy') => self::URLS['rope_toy'],
            str_contains($n, 'kong') => self::URLS['kong_toy'],
            str_contains($n, 'puzzle') => self::URLS['dog_toy_puzzle'],
            str_contains($n, 'catnip') || (str_contains($n, 'mouse') && $pet === 'cat') => self::URLS['catnip_mouse'],
            str_contains($n, 'feather wand') => self::URLS['feather_wand'],
            str_contains($n, 'scratching post') => self::URLS['scratching_post'],
            str_contains($n, 'laser pointer') => self::URLS['laser_toy'],
            str_contains($n, 'leather dog collar') || (str_contains($n, 'collar') && $pet === 'dog') => self::URLS['dog_collar'],
            str_contains($n, 'retractable') && str_contains($n, 'leash') => self::URLS['dog_leash'],
            str_contains($n, 'id tag') => self::URLS['dog_tag'],
            str_contains($n, 'harness') => self::URLS['dog_harness'],
            str_contains($n, 'cat collar') => self::URLS['cat_collar'],
            str_contains($n, 'carrier') => self::URLS['cat_carrier'],
            str_contains($n, 'litter box') => self::URLS['litter_box'],
            str_contains($n, 'bowl') => self::URLS['pet_bowls'],
            str_contains($n, 'dental chew') => self::URLS['dental_chews'],
            str_contains($n, 'hairball') => self::URLS['hairball_remedy'],
            str_contains($n, 'first aid') => self::URLS['first_aid'],
            str_contains($n, 'flea') || str_contains($n, 'tick') => self::URLS['flea_tick'],
            str_contains($n, 'slicker') || (str_contains($n, 'brush') && $pet === 'dog') => self::URLS['grooming_brush'],
            str_contains($n, 'grooming glove') => self::URLS['grooming_glove'],
            str_contains($n, 'shampoo') => self::URLS['pet_shampoo'],
            str_contains($n, 'nail clipper') => self::URLS['nail_clippers'],
            str_contains($n, 'orthopedic') || str_contains($n, 'dog bed') => self::URLS['dog_bed'],
            str_contains($n, 'cat bed') || str_contains($n, 'cozy cave') => self::URLS['cat_bed'],
            str_contains($n, 'blanket') => self::URLS['pet_blanket'],
            $c === 'food' && str_contains($n, 'treat') && $pet === 'dog' => self::URLS['dog_treats'],
            $c === 'food' && str_contains($n, 'treat') && $pet === 'cat' => self::URLS['cat_treats'],
            $c === 'food' && str_contains($n, 'puppy') => self::URLS['puppy_food'],
            $c === 'food' && str_contains($n, 'kitten') => self::URLS['kitten_food'],
            $c === 'food' && $pet === 'dog' && (str_contains($n, 'salmon') || str_contains($n, 'grain-free')) => self::URLS['dog_food_salmon'],
            $c === 'food' && $pet === 'dog' => self::URLS['dog_food'],
            $c === 'food' && $pet === 'cat' && (str_contains($n, 'tuna') || str_contains($n, 'wet')) => self::URLS['cat_food_wet'],
            $c === 'food' && $pet === 'cat' => self::URLS['cat_food'],
            $c === 'toys' && $pet === 'cat' => self::URLS['cat_toy'],
            $c === 'toys' => self::URLS['dog_toy'],
            $c === 'accessories' && $pet === 'cat' => self::URLS['cat_accessory'],
            $c === 'accessories' && $pet === 'dog' => self::URLS['dog_accessory'],
            $c === 'health' => self::URLS['pet_health'],
            $c === 'grooming' => self::URLS['pet_grooming'],
            $c === 'bedding' => self::URLS['pet_bedding'],
            $pet === 'cat' => self::URLS['cat_toy'],
            default => self::URLS['dog_food'],
        };
    }
}
