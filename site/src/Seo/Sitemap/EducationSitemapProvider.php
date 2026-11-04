<?php

namespace App\Seo\Sitemap;

use App\Repository\Article\EducationRepository;
use App\Seo\SitemapEntry;

/**
 * The lists of the education institutions by their types (the institutions have no page of their own).
 */
final class EducationSitemapProvider extends AbstractSitemapProvider
{
    public function __construct(private readonly EducationRepository $education)
    {
    }

    public function section(): string
    {
        return 'education';
    }

    public function entries(): iterable
    {
        foreach ($this->education->typeListRows() as $type) {
            yield new SitemapEntry('/oktatasi-intezmenyek/' . $type['slug'], $this->timestamp($type['updated']));
        }
    }
}
