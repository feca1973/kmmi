<?php

namespace App\Twig;

use App\Service\PlainText;
use Twig\Attribute\AsTwigFilter;

/**
 * Text filters for the values stored as HTML (titles keep entities like "&#8222;", leads are editor HTML).
 */
class TextFilters
{
    public function __construct(private readonly PlainText $text)
    {
    }

    /**
     * The visible text of an HTML value: no tags, no entities, single spaces. For attributes, meta tags and excerpts
     * (the result is escaped by Twig as usual).
     */
    #[AsTwigFilter('plain_text')]
    public function plainText(?string $html): string
    {
        return $this->text->from($html);
    }
}
