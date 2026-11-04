<?php

namespace App\Seo\Sitemap;

use App\Repository\Article\ActorRepository;
use App\Seo\PublicPaths;
use App\Seo\SitemapEntry;

/**
 * The lists of the portrait gallery (artists, writers, folk artists) and its people.
 */
final class ActorSitemapProvider extends AbstractSitemapProvider
{
    public function __construct(
        private readonly ActorRepository $actors,
        private readonly PublicPaths $paths,
    ) {
    }

    public function section(): string
    {
        return 'actors';
    }

    public function entries(): iterable
    {
        foreach ($this->actors->typeListRows() as $type) {
            yield new SitemapEntry('/arckepcsarnok/' . $type['slug'], $this->timestamp($type['updated']));
        }

        foreach ($this->actors->rows() as $actor) {
            yield new SitemapEntry(
                $this->paths->actor($actor['typeSlug'], $actor['slug']),
                $this->timestamp($actor['updated'] ?? $actor['published'])
            );
        }
    }
}
