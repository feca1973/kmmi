<?php

namespace App\Service;

/**
 * The visible text of a value stored as HTML: no tags, no entities, single spaces.
 */
class PlainText
{
    public function from(?string $html): string
    {
        $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}
