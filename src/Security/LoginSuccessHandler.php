<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

final class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private readonly RouterInterface $router,
    ) {}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $roles = $token->getRoleNames();

        if (\in_array('ROLE_ADMIN', $roles, true)) {
            return new RedirectResponse($this->router->generate('app_dashboard'));
        }

        if (\in_array('ROLE_STAFF', $roles, true)) {
            return new RedirectResponse($this->router->generate('app_admin').'#products');
        }

        return new RedirectResponse($this->router->generate('app_home'));
    }
}

