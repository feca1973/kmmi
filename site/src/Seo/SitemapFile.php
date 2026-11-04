<?php

namespace App\Seo;

/**
 * One sitemap file of the index: a section (or one 50 000 URL part of a big section) and its entries.
 */
final class SitemapFile
{
    /**
     * @param string $name `articles`, or `articles-2` for the second part of a big section
     * @param list<SitemapEntry> $entries
     */
    public function __construct(
        public readonly string $name,
        public readonly array $entries,
    ) {
    }

    public function path(): string
    {
        return '/sitemap-' . $this->name . '.xml';
    }

    /**
     * The newest modification of the entries, null when none of them has one.
     */
    public function lastModified(): ?int
    {
        $times = array_map(static fn (SitemapEntry $entry): int => $entry->lastModified ?? 0, $this->entries);
        $latest = max([0, ...$times]);

        return $latest > 0 ? $latest : null;
    }
}
