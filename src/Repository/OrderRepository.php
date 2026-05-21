<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OrderRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Order::class);
	}

	/** @return list<Order> */
	public function findByUser(\App\Entity\User $user): array
	{
		return $this->createQueryBuilder('o')
			->andWhere('o.user = :user')
			->setParameter('user', $user)
			->orderBy('o.createdAt', 'DESC')
			->getQuery()
			->getResult();
	}
}



