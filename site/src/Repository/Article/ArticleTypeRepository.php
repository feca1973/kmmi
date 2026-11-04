<?php

namespace App\Repository\Article;

use App\Entity\Article\ArticleType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArticleType>
 */
class ArticleTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleType::class);
    }

    /**
     * All the article types with their translations, by name.
     *
     * @return list<ArticleType>
     */
    public function findAllWithTranslations(): array
    {
        /** @var list<ArticleType> $types */
        $types = $this->createQueryBuilder('type')
            ->select('type', 'translations')
            ->innerJoin('type.translations', 'translations')
            ->orderBy('translations.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $types;
    }

    public function findOneBySlug(string $slug): ?ArticleType
    {
        return $this->createQueryBuilder('type')
            ->select('type', 'translations')
            ->innerJoin('type.translations', 'translations')
            ->where('translations.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
