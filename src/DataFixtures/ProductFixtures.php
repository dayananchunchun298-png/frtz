<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Exported from local database on 2026-05-22.
 */
class ProductFixtures extends Fixture implements FixtureGroupInterface
{
    private const GROUP = 'local-export';

    private const ROWS = [
            ['ref' => 'product_72', 'name' => 'Premium Dog Food - Chicken & Rice', 'description' => 'High-quality dry dog food made with real chicken and brown rice. Perfect for adult dogs of all sizes.', 'price' => '45.99', 'category' => 'food', 'petType' => 'dog', 'stock' => 50, 'image' => 'https://images.unsplash.com/photo-1589924691995-400dc9ecc119?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_73', 'name' => 'Grain-Free Dog Food - Salmon', 'description' => 'Nutritious grain-free dog food with fresh salmon. Ideal for dogs with sensitive stomachs.', 'price' => '52.99', 'category' => 'food', 'petType' => 'dog', 'stock' => 35, 'image' => 'https://images.unsplash.com/photo-1598463310879-4804de022573?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_74', 'name' => 'Puppy Food - Lamb & Sweet Potato', 'description' => 'Specially formulated for growing puppies with lamb and sweet potato for optimal nutrition.', 'price' => '38.99', 'category' => 'food', 'petType' => 'dog', 'stock' => 42, 'image' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_75', 'name' => 'Dog Treats - Beef Jerky', 'description' => 'Delicious beef jerky treats perfect for training and rewarding your dog.', 'price' => '12.99', 'category' => 'food', 'petType' => 'dog', 'stock' => 75, 'image' => 'https://images.unsplash.com/photo-1615740431354-7b1aef7861cf?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_76', 'name' => 'Premium Cat Food - Tuna & Salmon', 'description' => 'High-quality wet cat food with real tuna and salmon. Rich in protein and omega-3.', 'price' => '24.99', 'category' => 'food', 'petType' => 'cat', 'stock' => 59, 'image' => 'https://images.unsplash.com/photo-1573865526739-10659fec78a5?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_77', 'name' => 'Grain-Free Cat Food - Chicken', 'description' => 'Natural grain-free cat food with real chicken. Perfect for cats with food sensitivities.', 'price' => '28.99', 'category' => 'food', 'petType' => 'cat', 'stock' => 45, 'image' => 'https://images.unsplash.com/photo-1596854407944-b87cf12b3039?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_78', 'name' => 'Kitten Food - Chicken & Liver', 'description' => 'Specially formulated for growing kittens with chicken and liver for healthy development.', 'price' => '22.99', 'category' => 'food', 'petType' => 'cat', 'stock' => 38, 'image' => 'https://images.unsplash.com/photo-1529770891173-567f266563d5?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_79', 'name' => 'Cat Treats - Salmon Bites', 'description' => 'Irresistible salmon-flavored treats that cats love. Great for training and bonding.', 'price' => '8.99', 'category' => 'food', 'petType' => 'cat', 'stock' => 80, 'image' => 'https://images.unsplash.com/photo-1493974671818-edad47c10353?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_80', 'name' => 'Interactive Dog Puzzle Toy', 'description' => 'Mental stimulation toy that keeps your dog engaged and entertained for hours.', 'price' => '18.99', 'category' => 'toys', 'petType' => 'dog', 'stock' => 25, 'image' => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_81', 'name' => 'Durable Rope Toy - Large', 'description' => 'Heavy-duty rope toy perfect for tug-of-war and chewing. Made from safe, non-toxic materials.', 'price' => '15.99', 'category' => 'toys', 'petType' => 'dog', 'stock' => 30, 'image' => 'https://images.unsplash.com/photo-1583511655857-d58736495a0f?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_82', 'name' => 'Tennis Ball Set - 6 Pack', 'description' => 'High-quality tennis balls perfect for fetch and play. Bright colors for easy visibility.', 'price' => '9.99', 'category' => 'toys', 'petType' => 'dog', 'stock' => 50, 'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_83', 'name' => 'Kong Classic Dog Toy', 'description' => 'The original Kong toy - virtually indestructible and perfect for stuffing with treats.', 'price' => '12.99', 'category' => 'toys', 'petType' => 'dog', 'stock' => 40, 'image' => 'https://images.unsplash.com/photo-1587300003388-59208cc556f6?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_84', 'name' => 'Catnip Mouse - 3 Pack', 'description' => 'Soft, catnip-filled mice that cats absolutely love. Great for play and exercise.', 'price' => '7.99', 'category' => 'toys', 'petType' => 'cat', 'stock' => 35, 'image' => 'https://images.unsplash.com/photo-1526336024174-e58f5cdd8e13?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_85', 'name' => 'Feather Wand Toy', 'description' => 'Interactive wand toy with feathers that mimics prey movement. Perfect for bonding with your cat.', 'price' => '11.99', 'category' => 'toys', 'petType' => 'cat', 'stock' => 28, 'image' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_86', 'name' => 'Cat Scratching Post', 'description' => 'Tall scratching post with sisal rope and a cozy perch. Saves your furniture from claws.', 'price' => '35.99', 'category' => 'toys', 'petType' => 'cat', 'stock' => 15, 'image' => 'https://images.unsplash.com/photo-1545249390-8aa2f7d40342?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_87', 'name' => 'Laser Pointer Toy', 'description' => 'Red laser pointer that provides endless entertainment for your cat. Battery included.', 'price' => '6.99', 'category' => 'toys', 'petType' => 'cat', 'stock' => 45, 'image' => 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_88', 'name' => 'Leather Dog Collar - Brown', 'description' => 'Premium leather dog collar with brass hardware. Adjustable and comfortable.', 'price' => '24.99', 'category' => 'accessories', 'petType' => 'dog', 'stock' => 20, 'image' => 'https://images.unsplash.com/photo-1600275665164-949553a2e0e0?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_89', 'name' => 'Retractable Dog Leash - 16ft', 'description' => 'Heavy-duty retractable leash with comfortable grip and smooth retraction mechanism.', 'price' => '19.99', 'category' => 'accessories', 'petType' => 'dog', 'stock' => 24, 'image' => 'https://images.unsplash.com/photo-1558785078-eca7d0ecf9f0?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_90', 'name' => 'Dog ID Tag - Engraved', 'description' => 'Stainless steel ID tag with custom engraving. Waterproof and durable.', 'price' => '8.99', 'category' => 'accessories', 'petType' => 'dog', 'stock' => 50, 'image' => 'https://images.unsplash.com/photo-1587300003388-59208cc556f6?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_91', 'name' => 'Dog Harness - No Pull', 'description' => 'Comfortable no-pull harness that discourages pulling while walking.', 'price' => '29.99', 'category' => 'accessories', 'petType' => 'dog', 'stock' => 11, 'image' => 'https://images.unsplash.com/photo-1583511655826-05700d62f0e2?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_92', 'name' => 'Cat Collar with Bell', 'description' => 'Adjustable cat collar with safety bell. Breakaway design for safety.', 'price' => '6.99', 'category' => 'accessories', 'petType' => 'cat', 'stock' => 34, 'image' => 'https://images.unsplash.com/photo-1513245543132-31f507417b26?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_93', 'name' => 'Cat Carrier - Soft Sided', 'description' => 'Comfortable soft-sided carrier with mesh windows and shoulder strap.', 'price' => '39.99', 'category' => 'accessories', 'petType' => 'cat', 'stock' => 12, 'image' => 'https://images.unsplash.com/photo-1544568100-847a948facb9?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_94', 'name' => 'Cat Litter Box - Covered', 'description' => 'Large covered litter box with odor control and easy-clean design.', 'price' => '32.99', 'category' => 'accessories', 'petType' => 'cat', 'stock' => 8, 'image' => 'https://images.unsplash.com/photo-1598134493179-51332e56807f?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_95', 'name' => 'Cat Food Bowl Set - 2 Pack', 'description' => 'Stainless steel food and water bowls. Non-slip base and easy to clean.', 'price' => '14.99', 'category' => 'accessories', 'petType' => 'cat', 'stock' => 29, 'image' => 'https://images.unsplash.com/photo-1608846894770-d6d71cae3f56?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_96', 'name' => 'Dog Dental Chews - 30 Pack', 'description' => 'Daily dental chews that help reduce plaque and tartar buildup.', 'price' => '16.99', 'category' => 'health', 'petType' => 'dog', 'stock' => 33, 'image' => 'https://images.unsplash.com/photo-1615740431354-7b1aef7861cf?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_97', 'name' => 'Cat Hairball Remedy', 'description' => 'Natural hairball remedy that helps prevent and eliminate hairballs.', 'price' => '11.99', 'category' => 'health', 'petType' => 'cat', 'stock' => 25, 'image' => 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_98', 'name' => 'Pet First Aid Kit', 'description' => 'Comprehensive first aid kit for dogs and cats. Essential for emergencies.', 'price' => '24.99', 'category' => 'health', 'petType' => 'all', 'stock' => 12, 'image' => 'https://images.unsplash.com/photo-1576201836106-c179f380e165?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_99', 'name' => 'Flea & Tick Prevention - Monthly', 'description' => 'Monthly topical treatment for flea and tick prevention.', 'price' => '18.99', 'category' => 'health', 'petType' => 'all', 'stock' => 40, 'image' => 'https://images.unsplash.com/photo-1587300003388-59208cc556f6?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_100', 'name' => 'Dog Grooming Brush - Slicker', 'description' => 'Professional slicker brush for removing tangles and loose hair.', 'price' => '12.99', 'category' => 'grooming', 'petType' => 'dog', 'stock' => 21, 'image' => 'https://images.unsplash.com/photo-1516734212186-a967f33d1f6d?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_101', 'name' => 'Cat Grooming Glove', 'description' => 'Gentle grooming glove that removes loose hair while petting your cat.', 'price' => '9.99', 'category' => 'grooming', 'petType' => 'cat', 'stock' => 28, 'image' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_102', 'name' => 'Pet Shampoo - Oatmeal', 'description' => 'Gentle oatmeal shampoo for sensitive skin. Safe for dogs and cats.', 'price' => '13.99', 'category' => 'grooming', 'petType' => 'all', 'stock' => 32, 'image' => 'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_103', 'name' => 'Nail Clippers - Professional', 'description' => 'Professional-grade nail clippers with safety guard for precise trimming.', 'price' => '15.99', 'category' => 'grooming', 'petType' => 'all', 'stock' => 20, 'image' => 'https://images.unsplash.com/photo-1560807707-8cc77767d783?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_104', 'name' => 'Dog Bed - Orthopedic', 'description' => 'Memory foam orthopedic dog bed for joint support and comfort.', 'price' => '79.99', 'category' => 'bedding', 'petType' => 'dog', 'stock' => 10, 'image' => 'https://images.unsplash.com/photo-1596495578065-6a8cd91d4787?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_105', 'name' => 'Cat Bed - Cozy Cave', 'description' => 'Soft, cave-style cat bed that provides security and warmth.', 'price' => '34.99', 'category' => 'bedding', 'petType' => 'cat', 'stock' => 18, 'image' => 'https://images.unsplash.com/photo-1519052537078-e6302a49648e?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
            ['ref' => 'product_106', 'name' => 'Pet Blanket - Fleece', 'description' => 'Soft fleece blanket perfect for keeping pets warm and comfortable.', 'price' => '19.99', 'category' => 'bedding', 'petType' => 'all', 'stock' => 25, 'image' => 'https://images.unsplash.com/photo-1450778862550-6aede4c644fe?w=400&h=400&fit=crop', 'isActive' => true, 'createdAt' => '2025-10-11 12:56:28', 'updatedAt' => null],
    ];

    public static function getGroups(): array
    {
        return [self::GROUP];
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::ROWS as $row) {
            $entity = new Product();
            $entity->setName($row['name']);
                $entity->setDescription($row['description']);
                $entity->setPrice($row['price']);
                $entity->setCategory($row['category']);
                $entity->setPetType($row['petType']);
                $entity->setStock($row['stock']);
                $entity->setImage($row['image']);
                $entity->setIsActive($row['isActive']);
                $entity->setCreatedAt(new \DateTime($row['createdAt']));
                if ($row['updatedAt'] !== null) {
                    $entity->setUpdatedAt(new \DateTime($row['updatedAt']));
                }
            $this->addReference($row['ref'], $entity, Product::class);
            $manager->persist($entity);
        }
        $manager->flush();
    }

}
