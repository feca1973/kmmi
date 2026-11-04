<?php

namespace App\Seo\Sitemap;

use App\Repository\Organization\OrganizationRepository;
use App\Seo\PublicPaths;
use App\Seo\SitemapEntry;

/**
 * The organization list and the organizations.
 */
final class OrganizationSitemapProvider extends AbstractSitemapProvider
{
    public function __construct(
        private readonly OrganizationRepository $organizations,
        private readonly PublicPaths $paths,
    ) {
    }

    public function section(): string
    {
        return 'organizations';
    }

    public function entries(): iterable
    {
        $organizations = $this->organizations->sitemapRows();
        if ($organizations !== []) {
            yield new SitemapEntry('/szervezetek');
        }

        foreach ($organizations as $organization) {
            yield new SitemapEntry(
                $this->paths->organization($organization['slug']),
                $this->timestamp($organization['updated'])
            );
        }
    }
}
