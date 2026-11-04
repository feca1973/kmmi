<?php

namespace App\Repository\Article;

use App\Entity\Article\BookCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookCategory>
 */
class BookCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookCategory::class);
    }

    public function findOneBySlug(string $slug): ?BookCategory
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
