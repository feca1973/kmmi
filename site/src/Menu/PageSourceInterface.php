<?php

namespace App\Menu;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * A kind of page the menu editor can offer as the target of a menu item. A new kind of page that can be a menu item
 * gets a source (a class that implements this interface is registered by itself); the sources are asked in the order
 * of their priority (#[AsTaggedItem]).
 */
#[AutoconfigureTag('app.menu_page_source')]
interface PageSourceInterface
{
    /**
     * @param string $term what the editor typed
     *
     * @return list<PageSuggestion>
     */
    public function find(string $term): array;
}
