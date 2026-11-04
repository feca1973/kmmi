<?php

namespace App\Repository\Event;

use App\Entity\Event\EventType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventType>
 */
class EventTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventType::class);
    }

    public function findOneBySlug(string $slug): ?EventType
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * The event types that have active events, with the last change of those.
     *
     * @return list<array{slug: string, updated: string|null}>
     */
    public function sitemapRows(): array
    {
        /** @var list<array{slug: string, updated: string|null}> $rows */
        $rows = $this->createQueryBuilder('type')
            ->select('type.slug AS slug', 'MAX(events.updateDate) AS updated')
            ->innerJoin('type.events', 'events')
            ->where('events.active = 1')
            ->groupBy('type.id', 'type.slug')
            ->orderBy('type.id', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }

    /**
     * The event types that have active events, with the number of those events.
     *
     * @return list<array{type: EventType, events: int}>
     */
    public function findUsedWithEventCount(): array
    {
        /** @var list<array{0: EventType, eventCount: int|string}> $rows */
        $rows = $this->createQueryBuilder('type')
            ->select('type', 'COUNT(events.id) AS eventCount')
            ->innerJoin('type.events', 'events')
            ->where('events.active = 1')
            ->groupBy('type.id')
            ->orderBy('type.name', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(
            static fn (array $row): array => ['type' => $row[0], 'events' => (int) $row['eventCount']],
            $rows
        );
    }
}
