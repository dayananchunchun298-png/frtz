<?php

namespace App\EventSubscriber;

use App\Entity\Appointment;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;

#[AsDoctrineListener(event: Events::prePersist)]
final class EntityOwnerSubscriber
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $user = $this->security->getUser();
        if (!$user instanceof User || $this->security->isGranted('ROLE_STAFF')) {
            return;
        }

        if ($entity instanceof Appointment && $entity->getUser() === null) {
            $entity->setUser($user);
        }
    }
}
