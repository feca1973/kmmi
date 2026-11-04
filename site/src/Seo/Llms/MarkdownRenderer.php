<?php

namespace App\Seo\Llms;

use App\Seo\PublicUrlBuilder;

/**
 * A page as Markdown: the title, its URL, the facts as a list and the sections.
 */
class MarkdownRenderer
{
    public function __construct(
        private readonly HtmlMarkdown $markdown,
        private readonly PublicUrlBuilder $urls,
    ) {
    }

    /**
     * @param int $level the level of the title heading (the sections are one deeper)
     */
    public function render(MarkdownPage $page, int $level = 1): string
    {
        $lines = [
            str_repeat('#', $level) . ' ' . $page->title,
            '',
            'URL: ' . $this->urls->absoluteUrl($page->path),
            '',
        ];

        foreach ($page->facts as $label => $value) {
            if ($value !== '') {
                $lines[] = '- ' . $label . ': ' . $value;
            }
        }

        foreach ($page->sections as $section) {
            $text = $this->markdown->convert($section['html']);
            if ($text === '') {
                continue;
            }
            $lines[] = '';
            if ($section['heading'] !== null) {
                $lines[] = str_repeat('#', $level + 1) . ' ' . $section['heading'];
                $lines[] = '';
            }
            $lines[] = $text;
        }

        return implode("\n", $lines) . "\n";
    }
}
