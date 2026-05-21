<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Entry point: loads all exported local database fixtures in dependency order.
 * Run: php bin/console doctrine:fixtures:load
 */
class AppFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // Entity fixtures in group "local-export" perform all persistence.
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ProductFixtures::class,
            ServiceFixtures::class,
            AppointmentFixtures::class,
            OrderFixtures::class,
            OrderItemFixtures::class,
            PaymentFixtures::class,
        ];
    }
}
