<?php

namespace App\Search;

use App\Entity\Article\Article;
use App\Entity\Event\Events;
use App\Entity\Monuments\Monument;
use App\Entity\Organization\Organization;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Rebuilds the whole search index from the content: it is needed after the database was imported (the live dump has no
 * index), or when something changed that the saving of a content does not see (the slug of an article type).
 */
class SearchReindexer
{
    /** The contents are loaded and indexed in batches, so the memory stays small (a text can be megabytes). */
    private const BATCH_SIZE = 50;

    /** @var list<class-string> */
    private const CONTENTS = [Article::class, Events::class, Monument::class, Organization::class];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SearchIndexer $indexer,
    ) {
    }

    /**
     * Indexes all the public contents and removes the rows of everything else. The index can be searched all the time:
     * the rows are replaced one by one, the ones that were not written in this run are removed at the end.
     *
     * @param (callable(int): void)|null $advance called with the number of the contents of a processed batch
     *
     * @return int the number of the contents on the search
     */
    public function rebuild(?callable $advance = null): int
    {
        $started = time();
        $indexed = 0;

        foreach (self::CONTENTS as $class) {
            foreach (array_chunk($this->ids($class), self::BATCH_SIZE) as $ids) {
                foreach ($this->entityManager->getRepository($class)->findBy(['id' => $ids]) as $owner) {
                    $indexed += $this->indexer->index($owner, $started) ? 1 : 0;
                }
                $this->entityManager->clear();
                if ($advance !== null) {
                    $advance(count($ids));
                }
            }
        }
        $this->indexer->removeOlderThan($started);

        return $indexed;
    }

    /**
     * @param class-string $class
     *
     * @return list<int>
     */
    public function ids(string $class): array
    {
        /** @var list<int> $ids */
        $ids = $this->entityManager
            ->createQuery('SELECT content.id FROM ' . $class . ' content ORDER BY content.id')
            ->getSingleColumnResult();

        return $ids;
    }

    /**
     * The number of the contents the rebuild goes through (for a progress bar).
     */
    public function total(): int
    {
        return array_sum(array_map(fn (string $class): int => count($this->ids($class)), self::CONTENTS));
    }
}
