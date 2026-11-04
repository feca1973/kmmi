<?php

namespace App\Repository\Article;

use App\Entity\Article\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * The base of the queries of the public pages about articles (news, library, portrait gallery, education): only the
 * active articles are public.
 *
 * @extends ServiceEntityRepository<Article>
 */
abstract class ArticleQueryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Active articles with their translation.
     */
    protected function createListBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('article')
            ->select('article', 'translations')
            ->innerJoin('article.translations', 'translations')
            ->where('article.active = 1');
    }
    /**
     * @param 'actorType'|'educationType' $relation the association of the article to the type
     *
     * @return list<array{slug: string, updated: string|null}>
     */
    protected function typeRows(string $relation, bool $publishedOnly): array
    {
        $builder = $this->createListBuilder()
            ->select('listType.slug AS slug', 'MAX(article.updateDate) AS updated')
            ->innerJoin('article.' . $relation, 'listType')
            ->groupBy('listType.id', 'listType.slug')
            ->orderBy('listType.id', 'ASC');
        if ($publishedOnly) {
            $builder->andWhere('article.pubDate <= CURRENT_TIMESTAMP()');
        }

        /** @var list<array{slug: string, updated: string|null}> $rows */
        $rows = $builder->getQuery()->getArrayResult();

        return $rows;
    }
}
