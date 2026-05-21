<?php

namespace App\DataFixtures;

use App\Entity\Service;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Exported from local database on 2026-05-22.
 */
class ServiceFixtures extends Fixture implements FixtureGroupInterface
{
    private const GROUP = 'local-export';

    private const ROWS = [
            ['ref' => 'service_1', 'name' => 'go', 'description' => 'gego', 'price' => '2121.00', 'petType' => 'cat', 'isActive' => true, 'createdAt' => '2025-10-14 08:24:02', 'updatedAt' => '2025-10-14 08:24:08'],
            ['ref' => 'service_2', 'name' => 'Full Grooming Package', 'description' => 'Bath, blow-dry, haircut, nail trim, and ear cleaning.', 'price' => '69.00', 'petType' => 'dog', 'isActive' => true, 'createdAt' => '2025-10-14 08:34:17', 'updatedAt' => null],
            ['ref' => 'service_3', 'name' => 'Basic Bath & Brush', 'description' => 'Gentle bath and thorough brush-out. Ideal between grooms.', 'price' => '35.00', 'petType' => 'dog', 'isActive' => true, 'createdAt' => '2025-10-14 08:34:17', 'updatedAt' => null],
            ['ref' => 'service_4', 'name' => 'Nail Trim', 'description' => 'Quick and safe nail trimming for comfort and mobility.', 'price' => '12.00', 'petType' => 'all', 'isActive' => true, 'createdAt' => '2025-10-14 08:34:17', 'updatedAt' => null],
            ['ref' => 'service_5', 'name' => 'Ear Cleaning', 'description' => 'Gentle ear cleaning to help prevent infections.', 'price' => '10.00', 'petType' => 'all', 'isActive' => true, 'createdAt' => '2025-10-14 08:34:17', 'updatedAt' => null],
            ['ref' => 'service_6', 'name' => 'Obedience Training - Starter', 'description' => 'Foundational training focusing on sit, stay, recall, and leash manners.', 'price' => '89.00', 'petType' => 'dog', 'isActive' => true, 'createdAt' => '2025-10-14 08:34:17', 'updatedAt' => null],
            ['ref' => 'service_7', 'name' => 'Cat Grooming - Deshedding', 'description' => 'Bath and deshedding for long-haired cats.', 'price' => '49.00', 'petType' => 'cat', 'isActive' => true, 'createdAt' => '2025-10-14 08:34:17', 'updatedAt' => null],
            ['ref' => 'service_8', 'name' => 'Dog Walking - 30 Minutes', 'description' => 'Individual neighborhood walk for exercise and enrichment.', 'price' => '20.00', 'petType' => 'dog', 'isActive' => true, 'createdAt' => '2025-10-14 08:34:17', 'updatedAt' => null],
            ['ref' => 'service_9', 'name' => 'rerere', 'description' => 'cold', 'price' => '2020.00', 'petType' => 'dog', 'isActive' => true, 'createdAt' => '2025-10-15 11:47:47', 'updatedAt' => '2025-10-15 11:47:48'],
    ];

    public static function getGroups(): array
    {
        return [self::GROUP];
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::ROWS as $row) {
            $entity = new Service();
            $entity->setName($row['name']);
                $entity->setDescription($row['description']);
                $entity->setPrice($row['price']);
                $entity->setPetType($row['petType']);
                $entity->setIsActive($row['isActive']);
                $entity->setCreatedAt(new \DateTime($row['createdAt']));
                if ($row['updatedAt'] !== null) {
                    $entity->setUpdatedAt(new \DateTime($row['updatedAt']));
                }
            $this->addReference($row['ref'], $entity, Service::class);
            $manager->persist($entity);
        }
        $manager->flush();
    }

}
