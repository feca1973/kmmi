<?php

namespace App\Seo\Sitemap;

use App\Repository\Doc\DocRepository;
use App\Seo\PublicPaths;
use App\Seo\SitemapEntry;

/**
 * The information pages (/informacio/{slug}).
 */
final class DocumentSitemapProvider extends AbstractSitemapProvider
{
    public function __construct(
        private readonly DocRepository $docs,
        private readonly PublicPaths $paths,
    ) {
    }

    public function section(): string
    {
        return 'documents';
    }

    public function entries(): iterable
    {
        foreach ($this->docs->sitemapRows() as $doc) {
            yield new SitemapEntry($this->paths->document($doc['slug']), $this->timestamp($doc['updated']));
        }
    }
}
