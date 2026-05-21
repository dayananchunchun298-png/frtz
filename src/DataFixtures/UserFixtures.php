<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Exported from local database on 2026-05-22.
 */
class UserFixtures extends Fixture implements FixtureGroupInterface
{
    private const GROUP = 'local-export';

    private const ROWS = [
            ['ref' => 'user_28', 'email' => 'dayananchunchun298@gmail.com', 'roles' => ['ROLE_STAFF'], 'password' => '$2y$13$PQK6udwO0vN8ktpS2aKIkOPskOAz8VRD4bGmTW3N2V4YuZD4EKMXu', 'isVerified' => true, 'verificationToken' => null, 'googleId' => '107202698046805165612'],
            ['ref' => 'user_29', 'email' => 'fritzmarvindayanan@gmail.com', 'roles' => ['ROLE_USER'], 'password' => '$2y$13$/p/Q25M/0Cyq5Tk5xBsUTegC16PubsMEUs0SyMlS/GIjAtTsZh.3u', 'isVerified' => true, 'verificationToken' => null, 'googleId' => '114926922116344875140'],
            ['ref' => 'user_30', 'email' => 'admin@pawcare.local', 'roles' => ['ROLE_ADMIN'], 'password' => '$2y$13$r5ZytXiG.G36Pm2xvUqZdeX1SOfWz5ldZ91zUB6Ylig9QNNlu922G', 'isVerified' => true, 'verificationToken' => null, 'googleId' => null],
            ['ref' => 'user_31', 'email' => 'testlogin@example.com', 'roles' => ['ROLE_USER'], 'password' => '$2y$13$.w45s8JLGn6WQV4YACltxuNdW6IRQO/FOuoj0FPKhgmHmXZCxSScS', 'isVerified' => false, 'verificationToken' => '66aced6f792575f8c06aba0111b0a713279e37ac543372e69eafe7eada195d1b', 'googleId' => null],
            ['ref' => 'user_32', 'email' => 'unverified153985279@example.com', 'roles' => ['ROLE_USER'], 'password' => '$2y$13$XwQVcGK1.CKo1Q.wroLXUOs4QL6hvfwrk73XeJZquUgfRTf/ozj3S', 'isVerified' => false, 'verificationToken' => '3b2a6d6387dd8a87522f0d10ac5c8473a7746ffa37f4bf34ba447efcedfeba79', 'googleId' => null],
            ['ref' => 'user_33', 'email' => 'customer@pawcare.local', 'roles' => ['ROLE_USER'], 'password' => '$2y$13$PAWHfOP68NR5hURYHG6x.eo3OwANnAvhGPf6UpvXwONfju2jlJBCq', 'isVerified' => true, 'verificationToken' => null, 'googleId' => null],
    ];

    public static function getGroups(): array
    {
        return [self::GROUP];
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::ROWS as $row) {
            $entity = new User();
            $entity->setEmail($row['email']);
                $entity->setRoles($row['roles']);
                $entity->setPassword($row['password']);
                $entity->setIsVerified($row['isVerified']);
                $entity->setVerificationToken($row['verificationToken']);
                $entity->setGoogleId($row['googleId']);
            $this->addReference($row['ref'], $entity, User::class);
            $manager->persist($entity);
        }
        $manager->flush();
    }

}
