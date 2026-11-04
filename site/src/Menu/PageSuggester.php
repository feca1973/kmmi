<?php

namespace App\Menu;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * The pages the menu editor offers while the editor types the target of a menu item: what the page sources find (the
 * main lists of the site, the article groups, the information pages, the galleries, the cities and the contents the
 * search finds), the first ones of every source first.
 */
class PageSuggester
{
    public const LIMIT = 12;

    private const MIN_LENGTH = 2;

    /**
     * @param iterable<PageSourceInterface> $sources
     */
    public function __construct(
        private readonly TitleMatcher $titles,
        #[AutowireIterator('app.menu_page_source')]
        private readonly iterable $sources,
    ) {
    }

    /**
     * @return list<PageSuggestion>
     */
    public function suggest(string $term): array
    {
        if (mb_strlen($this->titles->needle($term)) < self::MIN_LENGTH) {
            return [];
        }

        $suggestions = [];
        foreach ($this->sources as $source) {
            array_push($suggestions, ...$source->find($term));
        }

        return array_slice($suggestions, 0, self::LIMIT);
    }
}
