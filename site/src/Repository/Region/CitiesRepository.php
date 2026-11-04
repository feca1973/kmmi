<?php

namespace App\Repository\Region;

use App\Entity\Region\Cities;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cities>
 */
class CitiesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cities::class);
    }

    /**
     * The active cities by their names.
     *
     * @return Query<int, Cities>
     */
    public function queryActive(): Query
    {
        return $this->createActiveBuilder()
            ->orderBy('translations.title', 'ASC')
            ->addOrderBy('city.id', 'ASC')
            ->getQuery();
    }

    /**
     * All the active cities by their names.
     *
     * @return list<Cities>
     */
    public function findAllActive(): array
    {
        /** @var list<Cities> $cities */
        $cities = $this->queryActive()->getResult();

        return $cities;
    }

    /**
     * The pages of the active cities, for the sitemap.
     *
     * @return list<array{slug: string, regionSlug: string, updated: DateTime|null}>
     */
    public function sitemapRows(): array
    {
        /** @var list<array{slug: string, regionSlug: string, updated: DateTime|null}> $rows */
        $rows = $this->createActiveBuilder()
            ->select('translations.slug AS slug', 'regions.slug AS regionSlug', 'city.updateDate AS updated')
            ->orderBy('city.id', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }

    public function findActiveBySlug(string $slug): ?Cities
    {
        return $this->createActiveBuilder()
            ->andWhere('translations.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function createActiveBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('city')
            ->select('city', 'regions', 'translations')
            ->innerJoin('city.regions', 'regions')
            ->innerJoin('city.translations', 'translations')
            ->where('city.active = 1');
    }
}
