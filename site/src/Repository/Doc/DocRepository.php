<?php

namespace App\Repository\Doc;

use App\Entity\Doc\Doc;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Doc>
 */
class DocRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Doc::class);
    }

    public function findActiveBySlug(string $slug): ?Doc
    {
        return $this->createActiveBuilder()
            ->addSelect('attachments')
            ->leftJoin('doc.attachments', 'attachments')
            ->andWhere('translations.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * The departments and the contacts, listed beside the "About us" pages.
     *
     * @return list<Doc>
     */
    public function findDepartments(): array
    {
        /** @var list<Doc> $docs */
        $docs = $this->createActiveBuilder()
            ->andWhere('doc.id <= :last AND doc.id <> :about')
            ->setParameter('last', Doc::LAST_ABOUT_ID)
            ->setParameter('about', Doc::ABOUT_ID)
            ->orderBy('doc.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $docs;
    }

    /**
     * All the active documents in the order of their creation.
     *
     * @return list<Doc>
     */
    public function findAllActive(): array
    {
        /** @var list<Doc> $docs */
        $docs = $this->createActiveBuilder()->orderBy('doc.id', 'ASC')->getQuery()->getResult();

        return $docs;
    }

    /**
     * The pages of the active documents, for the sitemap.
     *
     * @return list<array{slug: string, updated: DateTime|null}>
     */
    public function sitemapRows(): array
    {
        /** @var list<array{slug: string, updated: DateTime|null}> $rows */
        $rows = $this->createActiveBuilder()
            ->select('translations.slug AS slug', 'doc.updateDate AS updated')
            ->orderBy('doc.id', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }

    private function createActiveBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('doc')
            ->select('doc', 'translations')
            ->innerJoin('doc.translations', 'translations')
            ->where('doc.active = 1');
    }
}
