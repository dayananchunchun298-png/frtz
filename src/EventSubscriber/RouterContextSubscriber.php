<?php

namespace App\EventSubscriber;

use App\Service\AppUrlService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

/**
 * Forces the router to use APP_URL for absolute link generation (emails, OAuth).
 */
final class RouterContextSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RouterInterface $router,
        private AppUrlService $appUrlService,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 100],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $base = $this->appUrlService->getBaseUrl() ?? $this->baseUrlFromRequest($request);
        if ($base === null) {
            return;
        }

        $parsed = parse_url($base);
        if ($parsed === false || !isset($parsed['host'])) {
            return;
        }

        $scheme = $parsed['scheme'] ?? 'https';
        $context = $this->router->getContext();
        $context->setHost($parsed['host']);
        $context->setScheme($scheme);

        if (isset($parsed['port'])) {
            $port = (int) $parsed['port'];
            $context->setHttpPort($port);
            $context->setHttpsPort($port);
        } else {
            $context->setHttpPort($scheme === 'https' ? 443 : 80);
            $context->setHttpsPort(443);
        }
    }

    private function baseUrlFromRequest(Request $request): ?string
    {
        $host = $request->getHttpHost();
        if ($host === '') {
            return null;
        }

        return $request->getScheme().'://'.$host;
    }
}
