<?php

namespace App\Search;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

/**
 * The search on the `search_document` table with the full text indexes of MySQL (boolean mode). A hit is a page whose
 * title or text has all the words; the title weighs more (its own index: a word of the title counts once more, with the
 * weight, on top of the relevance in the title and the text); a page is not found before its publication.
 */
class FulltextSearchEngine implements SearchEngineInterface
{
    /** The legacy site listed at most this many hits (the Elasticsearch size). */
    public const MAX_HITS = 1000;

    private const TITLE_WEIGHT = 5;

    public function __construct(
        private readonly Connection $connection,
        private readonly BooleanQueryBuilder $queries,
    ) {
    }

    public function findIds(string $term): array
    {
        $query = $this->queries->build($term);
        if ($query === null) {
            return [];
        }

        $ids = $this->connection->fetchFirstColumn(
            'SELECT id FROM search_document
            WHERE MATCH(title, body_text) AGAINST (:query IN BOOLEAN MODE)
                AND (published_at IS NULL OR published_at <= UNIX_TIMESTAMP())
            ORDER BY MATCH(title) AGAINST (:query IN BOOLEAN MODE) * ' . self::TITLE_WEIGHT . '
                + MATCH(title, body_text) AGAINST (:query IN BOOLEAN MODE) DESC, published_at DESC, id DESC
            LIMIT ' . self::MAX_HITS,
            ['query' => $query]
        );

        return array_map('intval', $ids);
    }

    public function load(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $rows = $this->connection->fetchAllAssociativeIndexed(
            'SELECT id, title, url, lead_html, type FROM search_document WHERE id IN (:ids)',
            ['ids' => $ids],
            ['ids' => ArrayParameterType::INTEGER]
        );

        $hits = [];
        foreach ($ids as $id) {
            if (isset($rows[$id])) {
                $row = $rows[$id];
                $hits[] = new SearchHit(
                    (string) $row['title'],
                    (string) $row['url'],
                    (string) $row['lead_html'],
                    (string) $row['type']
                );
            }
        }

        return $hits;
    }
}
