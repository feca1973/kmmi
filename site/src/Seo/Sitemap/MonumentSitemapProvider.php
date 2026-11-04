<?php

namespace App\Seo\Sitemap;

use App\Repository\Monuments\MonumentRepository;
use App\Seo\PublicPaths;
use App\Seo\SitemapEntry;

/**
 * The monument list and the monuments.
 */
final class MonumentSitemapProvider extends AbstractSitemapProvider
{
    public function __construct(
        private readonly MonumentRepository $monuments,
        private readonly PublicPaths $paths,
    ) {
    }

    public function section(): string
    {
        return 'monuments';
    }

    public function entries(): iterable
    {
        $monuments = $this->monuments->sitemapRows();
        if ($monuments !== []) {
            yield new SitemapEntry('/emlekhelyek');
        }

        foreach ($monuments as $monument) {
            yield new SitemapEntry(
                $this->paths->monument($monument['typeSlug'], $monument['slug']),
                $this->timestamp($monument['updated'])
            );
        }
    }
}
