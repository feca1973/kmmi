<?php

namespace App\Repository\Article;

use App\Entity\Article\ActorType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ActorType>
 */
class ActorTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActorType::class);
    }

    public function findOneBySlug(string $slug): ?ActorType
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
