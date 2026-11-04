<?php

namespace App\Seo\Sitemap;

use App\Repository\Region\CitiesRepository;
use App\Seo\PublicPaths;
use App\Seo\SitemapEntry;

/**
 * The city list and the cities.
 */
final class CitySitemapProvider extends AbstractSitemapProvider
{
    public function __construct(
        private readonly CitiesRepository $cities,
        private readonly PublicPaths $paths,
    ) {
    }

    public function section(): string
    {
        return 'cities';
    }

    public function entries(): iterable
    {
        $cities = $this->cities->sitemapRows();
        if ($cities !== []) {
            yield new SitemapEntry('/regiok/telepulesek');
        }

        foreach ($cities as $city) {
            yield new SitemapEntry(
                $this->paths->city($city['regionSlug'], $city['slug']),
                $this->timestamp($city['updated'])
            );
        }
    }
}
