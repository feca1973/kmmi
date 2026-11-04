<?php

namespace App\Menu;

use Symfony\Component\String\UnicodeString;

/**
 * Finds a typed word in a title the way an editor expects it: without regard to the case and to the accents ("rolunk"
 * finds "Rólunk").
 */
class TitleMatcher
{
    /**
     * What the editor typed, lower case and without accents.
     */
    public function needle(string $term): string
    {
        return $this->fold($term);
    }

    public function matches(string $title, string $needle): bool
    {
        return str_contains($this->fold($title), $needle);
    }

    private function fold(string $text): string
    {
        return (new UnicodeString(trim($text)))->ascii()->lower()->toString();
    }
}
