<?php

namespace App\Repository\Article;

use App\Entity\Article\Article;
use App\Entity\Article\ArticleType;
use DateTime;
use Doctrine\ORM\Query;

/**
 * Queries of the public pages; only the active articles are public.
 *
 */
class ArticleRepository extends ArticleQueryRepository
{
    /**
     * The published articles of an article type, newest first.
     *
     * @return Query<int, Article>
     */
    public function queryPublishedByTypeSlug(string $slug): Query
    {
        return $this->createListBuilder()
            ->innerJoin('article.articleType', 'type')
            ->innerJoin('type.translations', 'typeTranslations')
            ->andWhere('typeTranslations.slug = :slug')
            ->andWhere('article.pubDate < CURRENT_TIMESTAMP()')
            ->setParameter('slug', $slug)
            ->orderBy('article.pubDate', 'DESC')
            ->addOrderBy('article.id', 'DESC')
            ->getQuery();
    }

    /**
     * All the articles of an article type, ordered by title.
     *
     * @return Query<int, Article>
     */
    public function queryByTypeId(int $typeId): Query
    {
        return $this->createListBuilder()
            ->andWhere('IDENTITY(article.articleType) = :typeId')
            ->setParameter('typeId', $typeId)
            ->orderBy('translations.title', 'ASC')
            ->addOrderBy('article.id', 'ASC')
            ->getQuery();
    }

    /**
     * The newest published articles of an article type.
     *
     * @return list<Article>
     */
    public function findLatest(int $typeId, int $limit): array
    {
        /** @var list<Article> $articles */
        $articles = $this->createListBuilder()
            ->addSelect('type', 'typeTranslations')
            ->innerJoin('article.articleType', 'type')
            ->innerJoin('type.translations', 'typeTranslations')
            ->andWhere('type.id = :typeId')
            ->andWhere('article.pubDate <= CURRENT_TIMESTAMP()')
            ->setParameter('typeId', $typeId)
            ->orderBy('article.pubDate', 'DESC')
            ->addOrderBy('article.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $articles;
    }

    /**
     * The published articles that have a page of their own (/cikkek/{type}/{slug}), for the sitemap: the churches are
     * listed on one page, the folk art has its own URLs.
     *
     * @return list<array{slug: string, typeSlug: string, typeId: int, updated: DateTime|null, published: DateTime}>
     */
    public function sitemapRows(): array
    {
        /** @var list<array{slug: string, typeSlug: string, typeId: int, updated: DateTime|null, published: DateTime}> $rows */
        $rows = $this->createListBuilder()
            ->select(
                'translations.slug AS slug',
                'typeTranslations.slug AS typeSlug',
                'type.id AS typeId',
                'article.updateDate AS updated',
                'article.pubDate AS published'
            )
            ->innerJoin('article.articleType', 'type')
            ->innerJoin('type.translations', 'typeTranslations')
            ->andWhere('article.pubDate <= CURRENT_TIMESTAMP()')
            ->andWhere('type.id <> :churches')
            ->setParameter('churches', ArticleType::CHURCHES_ID)
            ->orderBy('article.id', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }

    /**
     * The article types that have published articles, with the last change of those.
     *
     * @return list<array{typeSlug: string, typeId: int, updated: string|null}>
     */
    public function typeSitemapRows(): array
    {
        /** @var list<array{typeSlug: string, typeId: int, updated: string|null}> $rows */
        $rows = $this->createListBuilder()
            ->select('typeTranslations.slug AS typeSlug', 'type.id AS typeId', 'MAX(article.updateDate) AS updated')
            ->innerJoin('article.articleType', 'type')
            ->innerJoin('type.translations', 'typeTranslations')
            ->andWhere('article.pubDate <= CURRENT_TIMESTAMP()')
            ->groupBy('type.id', 'typeTranslations.slug')
            ->orderBy('type.id', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }

    public function findActiveBySlug(string $slug): ?Article
    {
        return $this->createListBuilder()
            ->addSelect('type', 'typeTranslations')
            ->leftJoin('article.articleType', 'type')
            ->leftJoin('type.translations', 'typeTranslations')
            ->andWhere('translations.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * All the published library items, the actors and the institutions (the permanent content of the llms-full.txt).
     *
     * @param 'bookCategory'|'actorType'|'educationType' $relation the association of the article to its type
     *
     * @return list<Article>
     */
    public function findAllOfType(string $relation): array
    {
        /** @var list<Article> $articles */
        $articles = $this->createListBuilder()
            ->addSelect('listType')
            ->innerJoin('article.' . $relation, 'listType')
            ->andWhere('article.pubDate <= CURRENT_TIMESTAMP()')
            ->orderBy('translations.title', 'ASC')
            ->addOrderBy('article.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $articles;
    }
}
