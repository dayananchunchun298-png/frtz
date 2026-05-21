<?php

namespace App\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Builds absolute URLs using APP_URL when set (Docker / Railway behind a proxy).
 */
final class AppUrlService
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private string $appUrl = '',
    ) {}

    public function getBaseUrl(): ?string
    {
        $url = rtrim(trim($this->appUrl), '/');

        return $url !== '' ? $url : null;
    }

    public function absoluteUrl(string $route, array $parameters = []): string
    {
        $path = $this->urlGenerator->generate($route, $parameters);
        $base = $this->getBaseUrl();

        if ($base !== null) {
            return $base.$path;
        }

        return $this->urlGenerator->generate($route, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
