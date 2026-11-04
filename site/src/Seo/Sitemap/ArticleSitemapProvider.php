<?php

namespace App\Seo\Sitemap;

use App\Entity\Article\ArticleType;
use App\Repository\Article\ArticleRepository;
use App\Seo\PublicPaths;
use App\Seo\SitemapEntry;

/**
 * The article lists (news, the other types, churches, folk art) and the articles.
 */
final class ArticleSitemapProvider extends AbstractSitemapProvider
{
    public function __construct(
        private readonly ArticleRepository $articles,
        private readonly PublicPaths $paths,
    ) {
    }

    public function section(): string
    {
        return 'articles';
    }

    public function entries(): iterable
    {
        foreach ($this->articles->typeSitemapRows() as $type) {
            $updated = $this->timestamp($type['updated']);
            yield new SitemapEntry('/cikkek/' . $type['typeSlug'], $updated);
            if ($type['typeId'] === ArticleType::CHURCHES_ID) {
                yield new SitemapEntry('/egyhazak', $updated);
            }
            if ($type['typeId'] === ArticleType::FOLK_ART_ID) {
                yield new SitemapEntry('/nepmuveszet', $updated);
            }
        }

        foreach ($this->articles->sitemapRows() as $article) {
            yield new SitemapEntry(
                $this->paths->article($article['typeId'], $article['typeSlug'], $article['slug']),
                $this->timestamp($article['updated'] ?? $article['published'])
            );
        }
    }
}
