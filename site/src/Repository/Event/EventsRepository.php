<?php

namespace App\Repository\Event;

use App\Entity\Event\Events;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Events>
 */
class EventsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Events::class);
    }

    /**
     * The newest active events.
     *
     * @return list<Events>
     */
    public function findLatest(int $limit): array
    {
        /** @var list<Events> $events */
        $events = $this->createActiveBuilder()
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $events;
    }

    /**
     * The active events (of an event type, if the slug is given), newest first.
     *
     * @return Query<int, Events>
     */
    public function queryActive(?string $typeSlug = null): Query
    {
        $builder = $this->createActiveBuilder();
        if ($typeSlug !== null) {
            $builder->andWhere('type.slug = :typeSlug')->setParameter('typeSlug', $typeSlug);
        }

        return $builder->getQuery();
    }

    /**
     * All the active events, newest first.
     *
     * @return list<Events>
     */
    public function findAllActive(): array
    {
        /** @var list<Events> $events */
        $events = $this->createActiveBuilder()->getQuery()->getResult();

        return $events;
    }

    /**
     * The pages of the active events, for the sitemap.
     *
     * @return list<array{slug: string, typeSlug: string, updated: DateTime|null}>
     */
    public function sitemapRows(): array
    {
        /** @var list<array{slug: string, typeSlug: string, updated: DateTime|null}> $rows */
        $rows = $this->createActiveBuilder()
            ->select('translations.slug AS slug', 'type.slug AS typeSlug', 'event.updateDate AS updated')
            ->innerJoin('event.city', 'city')
            ->resetDQLPart('orderBy')
            ->orderBy('event.id', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }

    public function findActiveBySlug(string $slug): ?Events
    {
        return $this->createActiveBuilder()
            ->addSelect('city', 'cityTranslations')
            ->innerJoin('event.city', 'city')
            ->innerJoin('city.translations', 'cityTranslations')
            ->andWhere('translations.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Active events with their type, translation and place, ordered by the beginning.
     */
    private function createActiveBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('event')
            ->select('event', 'type', 'translations', 'place')
            ->innerJoin('event.type', 'type')
            ->innerJoin('event.translations', 'translations')
            ->innerJoin('event.place', 'place')
            ->where('event.active = 1')
            ->orderBy('event.dateFrom', 'DESC')
            ->addOrderBy('event.id', 'DESC');
    }
}
