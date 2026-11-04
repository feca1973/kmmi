<?php

namespace App\Repository\Article;

use App\Entity\Article\Article;
use DateTime;
use Doctrine\ORM\Query;

/**
 * The queries of the portrait gallery pages (/arckepcsarnok).
 */
class ActorRepository extends ArticleQueryRepository
{
    /**
     * The published people of an actor type (the portrait gallery), by their names.
     *
     * @return Query<int, Article>
     */
    public function queryByType(string $typeSlug): Query
    {
        return $this->createListBuilder()
            ->addSelect('city', 'cityTranslations', 'regions')
            ->innerJoin('article.actorType', 'actorType')
            ->leftJoin('article.city', 'city')
            ->leftJoin('city.translations', 'cityTranslations')
            ->leftJoin('city.regions', 'regions')
            ->andWhere('actorType.slug = :slug')
            ->andWhere('article.pubDate < CURRENT_TIMESTAMP()')
            ->setParameter('slug', $typeSlug)
            ->orderBy('translations.title', 'ASC')
            ->addOrderBy('article.id', 'ASC')
            ->getQuery();
    }


    /**
     * The published people of the portrait gallery, for the sitemap (/arckepcsarnok/{type}/{slug}).
     *
     * @return list<array{slug: string, typeSlug: string, updated: DateTime|null, published: DateTime}>
     */
    public function rows(): array
    {
        /** @var list<array{slug: string, typeSlug: string, updated: DateTime|null, published: DateTime}> $rows */
        $rows = $this->createListBuilder()
            ->select(
                'translations.slug AS slug',
                'actorType.slug AS typeSlug',
                'article.updateDate AS updated',
                'article.pubDate AS published'
            )
            ->innerJoin('article.actorType', 'actorType')
            ->andWhere('article.pubDate <= CURRENT_TIMESTAMP()')
            ->orderBy('article.id', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }


    /**
     * The actor types that have published people, with the last change of those (the list pages).
     *
     * @return list<array{slug: string, updated: string|null}>
     */
    public function typeListRows(): array
    {
        return $this->typeRows('actorType', true);
    }
}
