<?php

namespace App\Repository\Organization;

use App\Entity\Organization\Organization;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Organization>
 */
class OrganizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }

    /**
     * The active organizations by their names.
     *
     * @return Query<int, Organization>
     */
    public function queryActive(): Query
    {
        return $this->createActiveBuilder()
            ->orderBy('translations.title', 'ASC')
            ->addOrderBy('organization.id', 'ASC')
            ->getQuery();
    }

    /**
     * All the active organizations by their names.
     *
     * @return list<Organization>
     */
    public function findAllActive(): array
    {
        /** @var list<Organization> $organizations */
        $organizations = $this->queryActive()->getResult();

        return $organizations;
    }

    /**
     * The pages of the active organizations, for the sitemap.
     *
     * @return list<array{slug: string, updated: DateTime|null}>
     */
    public function sitemapRows(): array
    {
        /** @var list<array{slug: string, updated: DateTime|null}> $rows */
        $rows = $this->createActiveBuilder()
            ->select('translations.slug AS slug', 'organization.updateDate AS updated')
            ->orderBy('organization.id', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }

    public function findActiveBySlug(string $slug): ?Organization
    {
        return $this->createActiveBuilder()
            ->andWhere('translations.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function createActiveBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('organization')
            ->select('organization', 'type', 'translations')
            ->innerJoin('organization.type', 'type')
            ->innerJoin('organization.translations', 'translations')
            ->where('organization.active = 1');
    }
}
