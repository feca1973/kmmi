<?php

namespace App\Repository\Unique;

use App\Entity\Unique\UniqueItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UniqueItem>
 */
class UniqueItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UniqueItem::class);
    }

    /**
     * The contents of all the items, by their slugs.
     *
     * @return array<string, string>
     */
    public function findAllContents(): array
    {
        /** @var list<array{slug: string, content: string|null}> $rows */
        $rows = $this->createQueryBuilder('item')
            ->select('item.slug', 'item.content')
            ->getQuery()
            ->getArrayResult();

        $contents = [];
        foreach ($rows as $row) {
            $contents[$row['slug']] = (string) $row['content'];
        }

        return $contents;
    }
}
