<?php

namespace App\Seo;

/**
 * One URL of the sitemap: its path and, when it is known, the last modification (unix timestamp). Without a reliable
 * time the `lastmod` element is left out.
 */
final class SitemapEntry
{
    public function __construct(
        public readonly string $path,
        public readonly ?int $lastModified = null,
    ) {
    }
}
