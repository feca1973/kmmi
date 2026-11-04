<?php

namespace App\Search;

/**
 * A page as the search index stores it: what a row of `search_document` is made of.
 */
final class SearchEntry
{
    /**
     * @param string $lead the lead as the result list shows it (HTML)
     * @param string $body the searched text of the page (no tags)
     */
    public function __construct(
        public readonly string $type,
        public readonly int $entityId,
        public readonly string $title,
        public readonly string $lead,
        public readonly string $body,
        public readonly string $url,
        public readonly ?int $publishedAt = null,
    ) {
    }
}
