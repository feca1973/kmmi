<?php

namespace App\Repository\Article;

use App\Entity\Article\Article;
use DateTime;
use Doctrine\ORM\Query;

/**
 * The queries of the library pages (/konyvtar).
 */
class LibraryRepository extends ArticleQueryRepository
{
    /**
     * The published items of a library category, newest first.
     *
     * @return Query<int, Article>
     */
    public function queryItems(string $categorySlug): Query
    {
        return $this->createListBuilder()
            ->innerJoin('article.bookCategory', 'category')
            ->andWhere('category.slug = :slug')
            ->andWhere('article.pubDate < CURRENT_TIMESTAMP()')
            ->setParameter('slug', $categorySlug)
            ->orderBy('article.pubDate', 'DESC')
            ->addOrderBy('article.id', 'DESC')
            ->getQuery();
    }


    /**
     * The published library items, for the sitemap (/konyvtar/{category}/reszletes/{slug}).
     *
     * @return list<array{slug: string, categorySlug: string, updated: DateTime|null, published: DateTime}>
     */
    public function rows(): array
    {
        /** @var list<array{slug: string, categorySlug: string, updated: DateTime|null, published: DateTime}> $rows */
        $rows = $this->createListBuilder()
            ->select(
                'translations.slug AS slug',
                'category.slug AS categorySlug',
                'article.updateDate AS updated',
                'article.pubDate AS published'
            )
            ->innerJoin('article.bookCategory', 'category')
            ->andWhere('article.pubDate <= CURRENT_TIMESTAMP()')
            ->orderBy('article.id', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }


    /**
     * The library categories that have published items, with the last change of those.
     *
     * @return list<array{slug: string, parentSlug: string|null, updated: string|null}>
     */
    public function categoryRows(): array
    {
        /** @var list<array{slug: string, parentSlug: string|null, updated: string|null}> $rows */
        $rows = $this->createListBuilder()
            ->select('category.slug AS slug', 'parent.slug AS parentSlug', 'MAX(article.updateDate) AS updated')
            ->innerJoin('article.bookCategory', 'category')
            ->leftJoin('category.parent', 'parent')
            ->andWhere('article.pubDate <= CURRENT_TIMESTAMP()')
            ->groupBy('category.id', 'category.slug', 'parent.slug')
            ->orderBy('category.id', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }
}
