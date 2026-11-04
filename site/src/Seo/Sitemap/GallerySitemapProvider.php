<?php

namespace App\Seo\Sitemap;

use App\Repository\Gallery\GalleryRepository;
use App\Seo\PublicPaths;
use App\Seo\SitemapEntry;

/**
 * The gallery list and the galleries.
 */
final class GallerySitemapProvider extends AbstractSitemapProvider
{
    public function __construct(
        private readonly GalleryRepository $galleries,
        private readonly PublicPaths $paths,
    ) {
    }

    public function section(): string
    {
        return 'galleries';
    }

    public function entries(): iterable
    {
        $galleries = $this->galleries->sitemapRows();
        if ($galleries !== []) {
            yield new SitemapEntry('/kepek');
        }

        foreach ($galleries as $gallery) {
            yield new SitemapEntry($this->paths->gallery($gallery['slug']), $this->timestamp($gallery['updated']));
        }
    }
}
