<?php

namespace App\Seo\Sitemap;

use App\Repository\Article\LibraryRepository;
use App\Seo\PublicPaths;
use App\Seo\SitemapEntry;

/**
 * The lists of the library categories and the library items.
 */
final class LibrarySitemapProvider extends AbstractSitemapProvider
{
    public function __construct(
        private readonly LibraryRepository $library,
        private readonly PublicPaths $paths,
    ) {
    }

    public function section(): string
    {
        return 'library';
    }

    public function entries(): iterable
    {
        foreach ($this->library->categoryRows() as $category) {
            $path = $category['parentSlug'] === null
                ? '/konyvtar/' . $category['slug']
                : '/konyvtar/' . $category['parentSlug'] . '/' . $category['slug'];

            yield new SitemapEntry($path, $this->timestamp($category['updated']));
        }

        foreach ($this->library->rows() as $book) {
            yield new SitemapEntry(
                $this->paths->libraryItem($book['categorySlug'], $book['slug']),
                $this->timestamp($book['updated'] ?? $book['published'])
            );
        }
    }
}
