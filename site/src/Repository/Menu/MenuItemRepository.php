<?php

namespace App\Repository\Menu;

use App\Entity\Menu\MenuItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MenuItem>
 */
class MenuItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuItem::class);
    }

    /**
     * The top level items of a menu, in the order of their creation.
     *
     * @return list<MenuItem>
     */
    public function findRootItems(int $menuId): array
    {
        /** @var list<MenuItem> $items */
        $items = $this->createQueryBuilder('item')
            ->where('item.parent IS NULL')
            ->andWhere('IDENTITY(item.menu) = :menuId')
            ->setParameter('menuId', $menuId)
            ->orderBy('item.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $items;
    }
}
