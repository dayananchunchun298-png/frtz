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
    name: 'app:create-default-admin',
    description: 'Creates a default admin account if it does not exist.',
)]
final class CreateDefaultAdminCommand extends Command
{
    private const ADMIN_EMAIL = 'admin@pawcare.local';
    private const ADMIN_PASSWORD = 'Admin@12345';

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

        $existing = $this->userRepository->findOneBy(['email' => self::ADMIN_EMAIL]);
        if ($existing instanceof User) {
            $existing->setRoles(['ROLE_ADMIN']);
            $existing->setIsVerified(true);
            $existing->setVerificationToken(null);
            $existing->setPassword($this->passwordHasher->hashPassword($existing, self::ADMIN_PASSWORD));
            $this->entityManager->flush();

            $io->warning('Admin account already exists. Credentials, roles, and verification were refreshed.');
            $io->listing([
                'Email: '.self::ADMIN_EMAIL,
                'Password: '.self::ADMIN_PASSWORD,
                'Login URL: /login',
                'Dashboard URL: /dashboard',
            ]);

            return Command::SUCCESS;
        }

        $user = new User();
        $user->setEmail(self::ADMIN_EMAIL);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setIsVerified(true);
        $user->setVerificationToken(null);
        $user->setPassword($this->passwordHasher->hashPassword($user, self::ADMIN_PASSWORD));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('Default admin account created.');
        $io->listing([
            'Email: '.self::ADMIN_EMAIL,
            'Password: '.self::ADMIN_PASSWORD,
            'Login URL: /login',
            'Dashboard URL: /dashboard',
        ]);

        return Command::SUCCESS;
    }
}
