<?php

namespace App\Command;

use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-sample-services',
    description: 'Add sample pet care services (grooming, training, walking, etc.)',
)]
class AddSampleServicesCommand extends Command
{
	public function __construct(
		private EntityManagerInterface $entityManager
	) {
		parent::__construct();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new SymfonyStyle($input, $output);

		$services = [
			[
				'name' => 'Full Grooming Package',
				'description' => 'Bath, blow-dry, haircut, nail trim, and ear cleaning.',
				'price' => '69.00',
				'petType' => 'dog',
				'isActive' => true,
			],
			[
				'name' => 'Basic Bath & Brush',
				'description' => 'Gentle bath and thorough brush-out. Ideal between grooms.',
				'price' => '35.00',
				'petType' => 'dog',
				'isActive' => true,
			],
			[
				'name' => 'Nail Trim',
				'description' => 'Quick and safe nail trimming for comfort and mobility.',
				'price' => '12.00',
				'petType' => 'all',
				'isActive' => true,
			],
			[
				'name' => 'Ear Cleaning',
				'description' => 'Gentle ear cleaning to help prevent infections.',
				'price' => '10.00',
				'petType' => 'all',
				'isActive' => true,
			],
			[
				'name' => 'Obedience Training - Starter',
				'description' => 'Foundational training focusing on sit, stay, recall, and leash manners.',
				'price' => '89.00',
				'petType' => 'dog',
				'isActive' => true,
			],
			[
				'name' => 'Cat Grooming - Deshedding',
				'description' => 'Bath and deshedding for long-haired cats.',
				'price' => '49.00',
				'petType' => 'cat',
				'isActive' => true,
			],
			[
				'name' => 'Dog Walking - 30 Minutes',
				'description' => 'Individual neighborhood walk for exercise and enrichment.',
				'price' => '20.00',
				'petType' => 'dog',
				'isActive' => true,
			],
		];

		$io->title('Adding Sample Services to Database');

		foreach ($services as $serviceData) {
			$service = new Service();
			$service->setName($serviceData['name']);
			$service->setDescription($serviceData['description']);
			$service->setPrice($serviceData['price']);
			$service->setPetType($serviceData['petType']);
			$service->setIsActive($serviceData['isActive']);
			$service->setCreatedAt(new \DateTime());

			$this->entityManager->persist($service);
		}

		$this->entityManager->flush();

		$io->success(sprintf('Successfully added %d sample services to the database!', count($services)));

		return Command::SUCCESS;
	}
}


