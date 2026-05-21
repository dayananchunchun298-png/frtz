<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-demo-customer',
    description: 'Creates or refreshes demo customer accounts for web and mobile API login.',
)]
final class CreateDemoCustomerCommand extends Command
{
    /** @var list<array{email: string, password: string}> */
    private const DEMO_ACCOUNTS = [
        ['email' => 'customer@pawcare.local', 'password' => 'Customer@12345'],
        ['email' => 'fritzmarvindayanan@gmail.com', 'password' => 'Customer@12345'],
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $lines = [];

        foreach (self::DEMO_ACCOUNTS as $demo) {
            $user = $this->userRepository->findOneBy(['email' => $demo['email']]);
            if (!$user instanceof User) {
                $user = new User();
                $user->setEmail($demo['email']);
                $this->entityManager->persist($user);
            }

            $user->setRoles(['ROLE_USER']);
            $user->setIsVerified(true);
            $user->setVerificationToken(null);
            $user->setPassword($this->passwordHasher->hashPassword($user, $demo['password']));
            $lines[] = sprintf('%s / %s', $demo['email'], $demo['password']);
        }

        $this->entityManager->flush();

        $io->success('Demo customer account(s) ready for JWT and mobile login.');
        $io->listing($lines);
        $io->note('API login: POST /api/login with JSON { "email", "password" }');

        return Command::SUCCESS;
    }
}
