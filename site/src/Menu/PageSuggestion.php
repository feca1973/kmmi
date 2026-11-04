<?php

namespace App\Menu;

/**
 * A page the menu editor offers as the target of a menu item.
 */
final class PageSuggestion
{
    /**
     * @param string $kind what the page is (article, event, list ...), for the editor
     */
    public function __construct(
        public readonly string $title,
        public readonly string $url,
        public readonly string $kind,
    ) {
    }
}
