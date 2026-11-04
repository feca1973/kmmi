<?php

namespace App\Repository;

use App\Entity\Media;
use App\Enum\MediaType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Media>
 */
class MediaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    /**
     * The active audio or video items in the order of their creation.
     *
     * @return Query<int, Media>
     */
    public function queryActiveByType(MediaType $type): Query
    {
        return $this->createQueryBuilder('media')
            ->where('media.active = 1')
            ->andWhere('media.type = :type')
            ->setParameter('type', $type)
            ->orderBy('media.id', 'ASC')
            ->getQuery();
    }

    public function findActiveBySlug(string $slug): ?Media
    {
        return $this->findOneBy(['slug' => $slug, 'active' => true]);
    }

    /**
     * The pages of the active items, for the sitemap.
     *
     * @return list<array{slug: string, type: MediaType}>
     */
    public function sitemapRows(): array
    {
        /** @var list<array{slug: string, type: MediaType}> $rows */
        $rows = $this->createQueryBuilder('media')
            ->select('media.slug AS slug', 'media.type AS type')
            ->where('media.active = 1')
            ->orderBy('media.id', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }
}
