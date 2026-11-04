<?php

namespace App\Search;

use Doctrine\DBAL\Connection;

/**
 * Writes the rows of the search index (SQL on the `search_document` table). The row of a content is rebuilt from the
 * content, or removed when the content has no public page.
 */
class SearchIndexer
{
    public function __construct(
        private readonly Connection $connection,
        private readonly SearchEntryFactory $entries,
    ) {
    }

    /**
     * Brings the row of a content up to date.
     *
     * @return bool whether the content is on the search (false: it has no public page, its row is removed)
     */
    public function index(object $owner, ?int $stamp = null): bool
    {
        $entry = $this->entries->create($owner);
        if ($entry !== null) {
            $this->save($entry, $stamp ?? time());

            return true;
        }

        $key = $this->entries->key($owner);
        if ($key !== null) {
            $this->remove(...$key);
        }

        return false;
    }

    public function remove(string $type, int $entityId): void
    {
        $this->connection->executeStatement(
            'DELETE FROM search_document WHERE type = :type AND entity_id = :entityId',
            ['type' => $type, 'entityId' => $entityId]
        );
    }

    /**
     * Removes the rows that were not written since the stamp (the contents that are gone or not public any more).
     */
    public function removeOlderThan(int $stamp): int
    {
        return (int) $this->connection->executeStatement(
            'DELETE FROM search_document WHERE updated_at < :stamp',
            ['stamp' => $stamp]
        );
    }

    private function save(SearchEntry $entry, int $stamp): void
    {
        $this->connection->executeStatement(
            'INSERT INTO search_document (type, entity_id, title, lead_html, body_text, url, published_at, updated_at)
            VALUES (:type, :entityId, :title, :lead, :body, :url, :publishedAt, :stamp) AS new
            ON DUPLICATE KEY UPDATE title = new.title, lead_html = new.lead_html, body_text = new.body_text,
                url = new.url, published_at = new.published_at, updated_at = new.updated_at',
            [
                'type' => $entry->type,
                'entityId' => $entry->entityId,
                'title' => $entry->title,
                'lead' => $entry->lead,
                'body' => $entry->body,
                'url' => $entry->url,
                'publishedAt' => $entry->publishedAt,
                'stamp' => $stamp,
            ]
        );
    }
}
