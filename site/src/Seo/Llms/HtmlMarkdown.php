<?php

namespace App\Seo\Llms;

use App\Seo\PublicUrlBuilder;
use League\HTMLToMarkdown\HtmlConverter;

/**
 * The HTML of the editor as Markdown, with absolute links and images.
 */
class HtmlMarkdown
{
    private readonly HtmlConverter $converter;

    public function __construct(private readonly PublicUrlBuilder $urls)
    {
        $this->converter = new HtmlConverter([
            'strip_tags' => true,
            'header_style' => 'atx',
            'hard_break' => true,
            'remove_nodes' => 'script style',
        ]);
    }

    public function convert(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $markdown = trim($this->converter->convert($html));

        // ](/uploads/a.jpg) -> ](https://kmmi.org.ua/uploads/a.jpg)
        return (string) preg_replace_callback(
            '#\]\((/(?!/)[^)\s]*)#',
            fn (array $match): string => '](' . $this->urls->absoluteUrl($match[1]),
            $markdown
        );
    }
}
