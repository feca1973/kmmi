<?php

namespace App\Repository\Article;

use App\Entity\Article\EducationType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EducationType>
 */
class EducationTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EducationType::class);
    }

    public function findOneBySlug(string $slug): ?EducationType
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
