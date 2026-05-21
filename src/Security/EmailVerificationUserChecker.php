<?php

namespace App\Security;

use App\Service\EmailVerificationService;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class EmailVerificationUserChecker implements UserCheckerInterface
{
    public function __construct(
        private EmailVerificationService $emailVerificationService
    ) {}

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof \App\Entity\User) {
            return;
        }

        if ($this->emailVerificationService->needsVerification($user)) {
            throw new CustomUserMessageAccountStatusException('Please verify your email address before logging in.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // no-op
    }
}

