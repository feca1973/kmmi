<?php

namespace App\Repository\Gallery;

use App\Entity\Gallery\Gallery;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Gallery>
 */
class GalleryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gallery::class);
    }

    /**
     * The last updated active galleries.
     *
     * @return list<Gallery>
     */
    public function findLastUpdated(int $limit): array
    {
        /** @var list<Gallery> $galleries */
        $galleries = $this->createQueryBuilder('gallery')
            ->where('gallery.active = 1')
            ->orderBy('gallery.updateDate', 'DESC')
            ->addOrderBy('gallery.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $galleries;
    }

    /**
     * The active galleries, the last updated first.
     *
     * @return Query<int, Gallery>
     */
    public function queryActive(): Query
    {
        return $this->createQueryBuilder('gallery')
            ->where('gallery.active = 1')
            ->orderBy('gallery.updateDate', 'DESC')
            ->addOrderBy('gallery.id', 'DESC')
            ->getQuery();
    }

    /**
     * The pages of the active galleries that have images, for the sitemap.
     *
     * @return list<array{slug: string, updated: DateTime|null}>
     */
    public function sitemapRows(): array
    {
        /** @var list<array{slug: string, updated: DateTime|null}> $rows */
        $rows = $this->createPublicBuilder()
            ->select('gallery.slug AS slug', 'gallery.updateDate AS updated')
            ->orderBy('gallery.id', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }

    /**
     * The public galleries (active, with images) by their names.
     *
     * @return list<Gallery>
     */
    public function findAllPublic(): array
    {
        /** @var list<Gallery> $galleries */
        $galleries = $this->createPublicBuilder()->orderBy('gallery.name', 'ASC')->getQuery()->getResult();

        return $galleries;
    }

    /**
     * An active gallery with its images (a gallery without images is not public).
     */
    public function findActiveWithImages(string $slug): ?Gallery
    {
        return $this->createQueryBuilder('gallery')
            ->select('gallery', 'images')
            ->innerJoin('gallery.galleryImages', 'images')
            ->where('gallery.active = 1')
            ->andWhere('gallery.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * The active galleries that have images: a gallery without images is not public.
     */
    private function createPublicBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('gallery')
            ->where('gallery.active = 1')
            ->andWhere(
                'EXISTS (SELECT image.id FROM App\Entity\Gallery\GalleryImage image WHERE image.gallery = gallery)'
            );
    }
}
