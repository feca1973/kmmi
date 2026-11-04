<?php

namespace App\Menu\Source;

use App\Menu\PageSourceInterface;
use App\Menu\PageSuggestion;
use App\Menu\TitleMatcher;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * The main lists of the site that are not made of editable rows.
 */
#[AsTaggedItem(priority: 60)]
final class ListPageSource implements PageSourceInterface
{
    /**
     * Title => route.
     *
     * @var array<string, string>
     */
    private const LISTS = [
        'Kezdőlap' => 'app_homepage',
        'Rendezvények' => 'event_list',
        'Emlékhelyek' => 'monuments_list',
        'Szervezetek' => 'organization_list',
        'Települések' => 'cities_list',
        'Képtár' => 'gallery_list',
        'Videótár' => 'video_list',
        'Hangtár' => 'audio_list',
        'Népművészet' => 'folk_list',
        'Egyházak' => 'churches_list',
    ];

    public function __construct(
        private readonly TitleMatcher $titles,
        private readonly UrlGeneratorInterface $router,
    ) {
    }

    public function find(string $term): array
    {
        $needle = $this->titles->needle($term);
        $suggestions = [];
        foreach (self::LISTS as $title => $route) {
            if ($this->titles->matches($title, $needle)) {
                $suggestions[] = new PageSuggestion($title, $this->router->generate($route), 'Oldal');
            }
        }

        return $suggestions;
    }
}
