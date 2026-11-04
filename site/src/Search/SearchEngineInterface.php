<?php

namespace App\Search;

/**
 * The search of the public site. The engine is behind this interface (the implementation is the MySQL full text
 * index, the legacy site had Elasticsearch), so the pages do not know about it. The search is two steps, so that a page
 * of the list loads only its own hits: the ids of all the hits, then the hits of the ids.
 */
interface SearchEngineInterface
{
    /**
     * The ids of the hits for the term, the most relevant first (at most MAX_HITS).
     *
     * @return list<int>
     */
    public function findIds(string $term): array;

    /**
     * The hits of the ids, in the order of the ids.
     *
     * @param list<int> $ids
     *
     * @return list<SearchHit>
     */
    public function load(array $ids): array;
}
