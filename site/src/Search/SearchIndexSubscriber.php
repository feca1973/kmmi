<?php

namespace App\Search;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Keeps the search index up to date when the content is saved (in the admin). The changed contents are collected
 * before the flush and written after it, when they have their ids and are saved. The index is derived data: whatever
 * goes wrong with it, the content stays saved, the error is logged and `app:search:reindex` repairs the index.
 */
#[AsDoctrineListener(event: Events::onFlush)]
#[AsDoctrineListener(event: Events::postFlush)]
class SearchIndexSubscriber
{
    /** @var array<int, object> the contents to index, by object id */
    private array $changed = [];

    /** @var list<array{string, int}> the type and id of the removed contents */
    private array $removed = [];

    public function __construct(
        private readonly SearchEntryFactory $entries,
        private readonly SearchIndexer $indexer,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $this->changed = [];
        $this->removed = [];
        $unitOfWork = $args->getObjectManager()->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {
            $key = $this->entries->key($entity);
            if ($key !== null) {
                $this->removed[] = $key;
            }
        }

        $saved = [...$unitOfWork->getScheduledEntityInsertions(), ...$unitOfWork->getScheduledEntityUpdates()];
        foreach ($saved as $entity) {
            $owner = $this->entries->owner($entity);
            if ($owner !== null) {
                $this->changed[spl_object_id($owner)] = $owner;
            }
        }
    }

    public function postFlush(): void
    {
        $changed = $this->changed;
        $removed = $this->removed;
        $this->changed = [];
        $this->removed = [];

        try {
            foreach ($removed as [$type, $entityId]) {
                $this->indexer->remove($type, $entityId);
            }
            foreach ($changed as $owner) {
                $this->indexer->index($owner);
            }
        } catch (Throwable $exception) {
            $this->logger->error(
                'The search index could not be updated: ' . $exception->getMessage(),
                ['exception' => $exception]
            );
        }
    }
}
