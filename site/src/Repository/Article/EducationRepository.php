<?php

namespace App\Repository\Article;

use App\Entity\Article\Article;
use Doctrine\ORM\Query;

/**
 * The queries of the education pages (/oktatasi-intezmenyek).
 */
class EducationRepository extends ArticleQueryRepository
{
    /**
     * The institutions of an education type, by their names.
     *
     * @return Query<int, Article>
     */
    public function queryByType(string $typeSlug): Query
    {
        return $this->createListBuilder()
            ->innerJoin('article.educationType', 'educationType')
            ->andWhere('educationType.slug = :slug')
            ->setParameter('slug', $typeSlug)
            ->orderBy('translations.title', 'ASC')
            ->addOrderBy('article.id', 'ASC')
            ->getQuery();
    }


    /**
     * The education types that have institutions, with the last change of those (the list pages).
     *
     * @return list<array{slug: string, updated: string|null}>
     */
    public function typeListRows(): array
    {
        return $this->typeRows('educationType', false);
    }
}
