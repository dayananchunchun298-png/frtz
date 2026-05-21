<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
final class GoogleAuthenticator extends OAuth2Authenticator
{
    public function __construct(
        private ClientRegistry $clientRegistry,
        private EntityManagerInterface $entityManager,
        private RouterInterface $router,
        private UserPasswordHasherInterface $passwordHasher,
        private string $staffAllowedEmailDomain = '',
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'app_google_connect_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);
        /** @var GoogleUser $googleUser */
        $googleUser = $client->fetchUserFromToken($accessToken);

        $email = $googleUser->getEmail();
        if (!$email) {
            throw new AuthenticationException('Google did not return an email address.');
        }

        $this->assertAllowedStaffEmail($email);

        $googleId = (string) $googleUser->getId();

        return new SelfValidatingPassport(
            new UserBadge($email, function () use ($email, $googleId) {
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

                if (!$user instanceof User) {
                    $user = new User();
                    $user->setEmail($email);
                    $user->setRoles(['ROLE_STAFF']);
                    $user->setGoogleId($googleId);
                    $user->setIsVerified(true);
                    $user->setVerificationToken(null);
                    $plain = bin2hex(random_bytes(16));
                    $user->setPassword($this->passwordHasher->hashPassword($user, $plain));
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();

                    return $user;
                }

                $user->setGoogleId($googleId);
                $user->setIsVerified(true);
                $user->setVerificationToken(null);
                $user->ensureStaffRole();
                $this->entityManager->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->router->generate('app_staff_dashboard'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($request->hasSession()) {
            $request->getSession()->getFlashBag()->add('error', 'Google sign-in failed: '.$exception->getMessage());
        }

        return new RedirectResponse($this->router->generate('app_login'));
    }

    private function assertAllowedStaffEmail(string $email): void
    {
        $domain = trim($this->staffAllowedEmailDomain);
        if ($domain === '') {
            return;
        }

        $emailLower = strtolower($email);
        $suffix = strtolower($domain);
        if (!str_starts_with($suffix, '@')) {
            $suffix = '@'.$suffix;
        }

        if (!str_ends_with($emailLower, $suffix)) {
            throw new AuthenticationException('This Google account is not authorized for staff access.');
        }
    }
}
