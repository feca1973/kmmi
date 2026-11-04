<?php

namespace App\Seo\Llms;

/**
 * The knowledge of one public page: its title, its path, the short facts and the text sections (HTML, as edited).
 */
final class MarkdownPage
{
    /**
     * @param array<string, string> $facts label => value (empty values are left out)
     * @param list<array{heading: string|null, html: string}> $sections
     */
    public function __construct(
        public readonly string $title,
        public readonly string $path,
        public readonly array $facts,
        public readonly array $sections,
        public readonly ?int $lastModified = null,
    ) {
    }
}
