<?php

namespace App\Repository\Monuments;

use App\Entity\Monuments\Monument;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Monument>
 */
class MonumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Monument::class);
    }

    /**
     * The last updated active monuments.
     *
     * @return list<Monument>
     */
    public function findLastUpdated(int $limit): array
    {
        /** @var list<Monument> $monuments */
        $monuments = $this->createActiveBuilder()
            ->orderBy('monument.updateDate', 'DESC')
            ->addOrderBy('monument.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $monuments;
    }

    /**
     * The active monuments, the last updated first.
     *
     * @return Query<int, Monument>
     */
    public function queryActive(): Query
    {
        return $this->createActiveBuilder()
            ->orderBy('monument.updateDate', 'DESC')
            ->addOrderBy('monument.id', 'DESC')
            ->getQuery();
    }

    /**
     * All the active monuments, the last updated first.
     *
     * @return list<Monument>
     */
    public function findAllActive(): array
    {
        /** @var list<Monument> $monuments */
        $monuments = $this->queryActive()->getResult();

        return $monuments;
    }

    /**
     * The pages of the active monuments, for the sitemap.
     *
     * @return list<array{slug: string, typeSlug: string, updated: DateTime|null}>
     */
    public function sitemapRows(): array
    {
        /** @var list<array{slug: string, typeSlug: string, updated: DateTime|null}> $rows */
        $rows = $this->createActiveBuilder()
            ->select('monument.slug AS slug', 'type.slug AS typeSlug', 'monument.updateDate AS updated')
            ->orderBy('monument.id', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }

    public function findActiveBySlug(string $slug): ?Monument
    {
        return $this->createActiveBuilder()
            ->addSelect('gallery')
            ->leftJoin('monument.gallery', 'gallery')
            ->andWhere('monument.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Active monuments with all their classifications.
     */
    private function createActiveBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('monument')
            ->select('monument', 'type', 'existing', 'localization', 'submitter')
            ->innerJoin('monument.type', 'type')
            ->innerJoin('monument.existing', 'existing')
            ->innerJoin('monument.localization', 'localization')
            ->innerJoin('monument.submitter', 'submitter')
            ->where('monument.active = 1');
    }
}
