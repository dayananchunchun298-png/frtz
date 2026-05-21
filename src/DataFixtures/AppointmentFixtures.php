<?php

namespace App\DataFixtures;

use App\Entity\Appointment;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Exported from local database on 2026-05-22.
 */
class AppointmentFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    private const GROUP = 'local-export';

    private const ROWS = [
            ['ref' => 'appointment_1', 'name' => 'riley', 'appointmentDate' => '2025-10-06 00:21:00', 'petType' => 'dog', 'userRef' => null],
            ['ref' => 'appointment_3', 'name' => 'toti', 'appointmentDate' => '2026-03-04 07:00:00', 'petType' => 'cat', 'userRef' => null],
            ['ref' => 'appointment_5', 'name' => 'nino', 'appointmentDate' => '2029-11-11 20:21:00', 'petType' => 'cat', 'userRef' => null],
            ['ref' => 'appointment_6', 'name' => 'lowisss', 'appointmentDate' => '2025-10-14 07:00:00', 'petType' => 'dog', 'userRef' => null],
            ['ref' => 'appointment_7', 'name' => 'marr', 'appointmentDate' => '2029-11-22 12:12:00', 'petType' => 'dog', 'userRef' => null],
            ['ref' => 'appointment_8', 'name' => 'lobo', 'appointmentDate' => '2025-10-15 18:00:00', 'petType' => 'dog', 'userRef' => null],
            ['ref' => 'appointment_9', 'name' => 'lala', 'appointmentDate' => '2443-11-11 12:22:00', 'petType' => 'cat', 'userRef' => null],
            ['ref' => 'appointment_10', 'name' => 'jijiji', 'appointmentDate' => '2029-12-12 23:11:00', 'petType' => 'cat', 'userRef' => null],
            ['ref' => 'appointment_11', 'name' => 'frolly', 'appointmentDate' => '2026-05-21 08:00:00', 'petType' => 'dog', 'userRef' => 'user_33'],
            ['ref' => 'appointment_12', 'name' => 'thegreat', 'appointmentDate' => '2026-05-22 17:00:00', 'petType' => 'dog', 'userRef' => 'user_29'],
            ['ref' => 'appointment_13', 'name' => 'frollyychi', 'appointmentDate' => '2026-05-22 19:00:00', 'petType' => 'dog', 'userRef' => 'user_33'],
    ];

    public static function getGroups(): array
    {
        return [self::GROUP];
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::ROWS as $row) {
            $entity = new Appointment();
            $entity->setName($row['name']);
                $entity->setAppointmentDate(new \DateTime($row['appointmentDate']));
                $entity->setPetType($row['petType']);
                if ($row['userRef'] !== null) {
                    $entity->setUser($this->getReference($row['userRef'], User::class));
                }
            $this->addReference($row['ref'], $entity, Appointment::class);
            $manager->persist($entity);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            \App\DataFixtures\UserFixtures::class,
        ];
    }
}
