<?php

namespace App\Command;

use App\Entity\Product;
use App\Service\ProductImageMatcher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-sample-products',
    description: 'Add sample dog and cat products to the database',
)]
class AddSampleProductsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProductImageMatcher $imageMatcher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $products = [
            // Dog Food Products
            [
                'name' => 'Premium Dog Food - Chicken & Rice',
                'description' => 'High-quality dry dog food made with real chicken and brown rice. Perfect for adult dogs of all sizes.',
                'price' => '45.99',
                'category' => 'food',
                'petType' => 'dog',
                'stock' => 50,
                'image' => 'https://images.unsplash.com/photo-1589924691995-400dc9ecc119?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Grain-Free Dog Food - Salmon',
                'description' => 'Nutritious grain-free dog food with fresh salmon. Ideal for dogs with sensitive stomachs.',
                'price' => '52.99',
                'category' => 'food',
                'petType' => 'dog',
                'stock' => 35,
                'image' => 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Puppy Food - Lamb & Sweet Potato',
                'description' => 'Specially formulated for growing puppies with lamb and sweet potato for optimal nutrition.',
                'price' => '38.99',
                'category' => 'food',
                'petType' => 'dog',
                'stock' => 42,
                'image' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Dog Treats - Beef Jerky',
                'description' => 'Delicious beef jerky treats perfect for training and rewarding your dog.',
                'price' => '12.99',
                'category' => 'food',
                'petType' => 'dog',
                'stock' => 75,
                'image' => 'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],

            // Cat Food Products
            [
                'name' => 'Premium Cat Food - Tuna & Salmon',
                'description' => 'High-quality wet cat food with real tuna and salmon. Rich in protein and omega-3.',
                'price' => '24.99',
                'category' => 'food',
                'petType' => 'cat',
                'stock' => 60,
                'image' => 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Grain-Free Cat Food - Chicken',
                'description' => 'Natural grain-free cat food with real chicken. Perfect for cats with food sensitivities.',
                'price' => '28.99',
                'category' => 'food',
                'petType' => 'cat',
                'stock' => 45,
                'image' => 'https://images.unsplash.com/photo-1592194996308-7b43878e84a6?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Kitten Food - Chicken & Liver',
                'description' => 'Specially formulated for growing kittens with chicken and liver for healthy development.',
                'price' => '22.99',
                'category' => 'food',
                'petType' => 'cat',
                'stock' => 38,
                'image' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Cat Treats - Salmon Bites',
                'description' => 'Irresistible salmon-flavored treats that cats love. Great for training and bonding.',
                'price' => '8.99',
                'category' => 'food',
                'petType' => 'cat',
                'stock' => 80,
                'image' => 'https://images.unsplash.com/photo-1513245543132-31f507417b26?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],

            // Dog Toys
            [
                'name' => 'Interactive Dog Puzzle Toy',
                'description' => 'Mental stimulation toy that keeps your dog engaged and entertained for hours.',
                'price' => '18.99',
                'category' => 'toys',
                'petType' => 'dog',
                'stock' => 25,
                'image' => 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Durable Rope Toy - Large',
                'description' => 'Heavy-duty rope toy perfect for tug-of-war and chewing. Made from safe, non-toxic materials.',
                'price' => '15.99',
                'category' => 'toys',
                'petType' => 'dog',
                'stock' => 30,
                'image' => 'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Tennis Ball Set - 6 Pack',
                'description' => 'High-quality tennis balls perfect for fetch and play. Bright colors for easy visibility.',
                'price' => '9.99',
                'category' => 'toys',
                'petType' => 'dog',
                'stock' => 50,
                'image' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Kong Classic Dog Toy',
                'description' => 'The original Kong toy - virtually indestructible and perfect for stuffing with treats.',
                'price' => '12.99',
                'category' => 'toys',
                'petType' => 'dog',
                'stock' => 40,
                'image' => 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],

            // Cat Toys
            [
                'name' => 'Catnip Mouse - 3 Pack',
                'description' => 'Soft, catnip-filled mice that cats absolutely love. Great for play and exercise.',
                'price' => '7.99',
                'category' => 'toys',
                'petType' => 'cat',
                'stock' => 35,
                'image' => 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Feather Wand Toy',
                'description' => 'Interactive wand toy with feathers that mimics prey movement. Perfect for bonding with your cat.',
                'price' => '11.99',
                'category' => 'toys',
                'petType' => 'cat',
                'stock' => 28,
                'image' => 'https://images.unsplash.com/photo-1592194996308-7b43878e84a6?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Cat Scratching Post',
                'description' => 'Tall scratching post with sisal rope and a cozy perch. Saves your furniture from claws.',
                'price' => '35.99',
                'category' => 'toys',
                'petType' => 'cat',
                'stock' => 15,
                'image' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Laser Pointer Toy',
                'description' => 'Red laser pointer that provides endless entertainment for your cat. Battery included.',
                'price' => '6.99',
                'category' => 'toys',
                'petType' => 'cat',
                'stock' => 45,
                'image' => 'https://images.unsplash.com/photo-1513245543132-31f507417b26?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],

            // Dog Accessories
            [
                'name' => 'Leather Dog Collar - Brown',
                'description' => 'Premium leather dog collar with brass hardware. Adjustable and comfortable.',
                'price' => '24.99',
                'category' => 'accessories',
                'petType' => 'dog',
                'stock' => 20,
                'image' => 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Retractable Dog Leash - 16ft',
                'description' => 'Heavy-duty retractable leash with comfortable grip and smooth retraction mechanism.',
                'price' => '19.99',
                'category' => 'accessories',
                'petType' => 'dog',
                'stock' => 25,
                'image' => 'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Dog ID Tag - Engraved',
                'description' => 'Stainless steel ID tag with custom engraving. Waterproof and durable.',
                'price' => '8.99',
                'category' => 'accessories',
                'petType' => 'dog',
                'stock' => 60,
                'image' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Dog Harness - No Pull',
                'description' => 'Comfortable no-pull harness that discourages pulling while walking.',
                'price' => '29.99',
                'category' => 'accessories',
                'petType' => 'dog',
                'stock' => 18,
                'image' => 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],

            // Cat Accessories
            [
                'name' => 'Cat Collar with Bell',
                'description' => 'Adjustable cat collar with safety bell. Breakaway design for safety.',
                'price' => '6.99',
                'category' => 'accessories',
                'petType' => 'cat',
                'stock' => 40,
                'image' => 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Cat Carrier - Soft Sided',
                'description' => 'Comfortable soft-sided carrier with mesh windows and shoulder strap.',
                'price' => '39.99',
                'category' => 'accessories',
                'petType' => 'cat',
                'stock' => 12,
                'image' => 'https://images.unsplash.com/photo-1592194996308-7b43878e84a6?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Cat Litter Box - Covered',
                'description' => 'Large covered litter box with odor control and easy-clean design.',
                'price' => '32.99',
                'category' => 'accessories',
                'petType' => 'cat',
                'stock' => 8,
                'image' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Cat Food Bowl Set - 2 Pack',
                'description' => 'Stainless steel food and water bowls. Non-slip base and easy to clean.',
                'price' => '14.99',
                'category' => 'accessories',
                'petType' => 'cat',
                'stock' => 30,
                'image' => 'https://images.unsplash.com/photo-1513245543132-31f507417b26?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],

            // Health & Care Products
            [
                'name' => 'Dog Dental Chews - 30 Pack',
                'description' => 'Daily dental chews that help reduce plaque and tartar buildup.',
                'price' => '16.99',
                'category' => 'health',
                'petType' => 'dog',
                'stock' => 35,
                'image' => 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Cat Hairball Remedy',
                'description' => 'Natural hairball remedy that helps prevent and eliminate hairballs.',
                'price' => '11.99',
                'category' => 'health',
                'petType' => 'cat',
                'stock' => 25,
                'image' => 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Pet First Aid Kit',
                'description' => 'Comprehensive first aid kit for dogs and cats. Essential for emergencies.',
                'price' => '24.99',
                'category' => 'health',
                'petType' => 'all',
                'stock' => 15,
                'image' => 'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Flea & Tick Prevention - Monthly',
                'description' => 'Monthly topical treatment for flea and tick prevention.',
                'price' => '18.99',
                'category' => 'health',
                'petType' => 'all',
                'stock' => 40,
                'image' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],

            // Grooming Products
            [
                'name' => 'Dog Grooming Brush - Slicker',
                'description' => 'Professional slicker brush for removing tangles and loose hair.',
                'price' => '12.99',
                'category' => 'grooming',
                'petType' => 'dog',
                'stock' => 22,
                'image' => 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Cat Grooming Glove',
                'description' => 'Gentle grooming glove that removes loose hair while petting your cat.',
                'price' => '9.99',
                'category' => 'grooming',
                'petType' => 'cat',
                'stock' => 28,
                'image' => 'https://images.unsplash.com/photo-1592194996308-7b43878e84a6?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Pet Shampoo - Oatmeal',
                'description' => 'Gentle oatmeal shampoo for sensitive skin. Safe for dogs and cats.',
                'price' => '13.99',
                'category' => 'grooming',
                'petType' => 'all',
                'stock' => 32,
                'image' => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Nail Clippers - Professional',
                'description' => 'Professional-grade nail clippers with safety guard for precise trimming.',
                'price' => '15.99',
                'category' => 'grooming',
                'petType' => 'all',
                'stock' => 20,
                'image' => 'https://images.unsplash.com/photo-1513245543132-31f507417b26?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],

            // Bedding Products
            [
                'name' => 'Dog Bed - Orthopedic',
                'description' => 'Memory foam orthopedic dog bed for joint support and comfort.',
                'price' => '79.99',
                'category' => 'bedding',
                'petType' => 'dog',
                'stock' => 10,
                'image' => 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Cat Bed - Cozy Cave',
                'description' => 'Soft, cave-style cat bed that provides security and warmth.',
                'price' => '34.99',
                'category' => 'bedding',
                'petType' => 'cat',
                'stock' => 18,
                'image' => 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ],
            [
                'name' => 'Pet Blanket - Fleece',
                'description' => 'Soft fleece blanket perfect for keeping pets warm and comfortable.',
                'price' => '19.99',
                'category' => 'bedding',
                'petType' => 'all',
                'stock' => 25,
                'image' => 'https://images.unsplash.com/photo-1592194996308-7b43878e84a6?w=400&h=400&fit=crop&crop=center',
                'isActive' => true
            ]
        ];

        $io->title('Adding Sample Products to Database');

        foreach ($products as $productData) {
            $product = new Product();
            $product->setName($productData['name']);
            $product->setDescription($productData['description']);
            $product->setPrice($productData['price']);
            $product->setCategory($productData['category']);
            $product->setPetType($productData['petType']);
            $product->setStock($productData['stock']);
            $product->setImage($this->imageMatcher->resolve(
                $productData['name'],
                $productData['category'],
                $productData['petType'],
            ));
            $product->setIsActive($productData['isActive']);
            $product->setCreatedAt(new \DateTime());

            $this->entityManager->persist($product);
        }

        $this->entityManager->flush();

        $io->success(sprintf('Successfully added %d sample products to the database!', count($products)));

        return Command::SUCCESS;
    }
}
