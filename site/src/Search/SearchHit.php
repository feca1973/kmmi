<?php

namespace App\Search;

/**
 * One result of the search: the title, the path of the page, the lead as the list shows it (HTML) and the kind of the
 * content (article, event, monument, organization).
 */
final class SearchHit
{
    public function __construct(
        public readonly string $title,
        public readonly string $url,
        public readonly string $lead,
        public readonly string $type,
    ) {
    }
}
