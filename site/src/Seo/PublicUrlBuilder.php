<?php

namespace App\Seo;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Absolute URLs of the public site (sitemap, robots.txt, llms.txt, Markdown pages). The origin is the configured
 * `DEFAULT_URI` (https://kmmi.org.ua in production); without a valid one the origin of the current request.
 */
class PublicUrlBuilder
{
    public function __construct(
        private readonly RequestStack $requestStack,
        #[Autowire('%env(DEFAULT_URI)%')]
        private readonly string $publicSiteUrl,
    ) {
    }

    /**
     * The path unchanged when there is neither a configured origin nor a request (console).
     */
    public function absoluteUrl(string $path): string
    {
        $origin = $this->origin();

        return $origin === null ? $path : $origin . '/' . ltrim($path, '/');
    }

    private function origin(): ?string
    {
        $configured = rtrim(trim($this->publicSiteUrl), '/');
        if (preg_match('#^https?://[^/\s]+$#i', $configured) === 1) {
            return $configured;
        }

        $request = $this->requestStack->getCurrentRequest();

        return $request === null ? null : rtrim($request->getSchemeAndHttpHost(), '/');
    }
}
