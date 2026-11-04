<?php

namespace App\Seo;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * robots.txt: every crawler (the AI ones too) may read the public site, the admin and the search results are closed,
 * the sitemap and the llms.txt are announced. With SEO_INDEXING_ENABLED=0 (dev, staging) everything is closed.
 */
class RobotsTxtBuilder
{
    private const PRIVATE_PATHS = ['/admin/', '/search/', '/api/'];

    public function __construct(
        private readonly PublicUrlBuilder $urls,
        #[Autowire('%env(bool:SEO_INDEXING_ENABLED)%')]
        private readonly bool $indexingEnabled,
    ) {
    }

    public function build(): string
    {
        if (!$this->indexingEnabled) {
            return implode("\n", ['User-agent: *', 'Disallow: /', '']);
        }

        return implode("\n", [
            'User-agent: *',
            'Allow: /',
            ...array_map(static fn (string $path): string => 'Disallow: ' . $path, self::PRIVATE_PATHS),
            '',
            'Sitemap: ' . $this->urls->absoluteUrl('/sitemap.xml'),
            '',
        ]);
    }
}
