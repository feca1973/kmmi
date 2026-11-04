<?php

namespace App\Menu\Source;

use App\Menu\PageSourceInterface;
use App\Menu\PageSuggestion;
use App\Menu\TitleMatcher;
use App\Repository\Region\CitiesRepository;
use App\Seo\PublicPaths;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * The cities (/regiok/telepulesek/{region}/{slug}).
 */
#[AsTaggedItem(priority: 20)]
final class CityPageSource implements PageSourceInterface
{
    public function __construct(
        private readonly TitleMatcher $titles,
        private readonly CitiesRepository $cities,
        private readonly PublicPaths $paths,
    ) {
    }

    public function find(string $term): array
    {
        $needle = $this->titles->needle($term);
        $suggestions = [];
        foreach ($this->cities->findAllActive() as $city) {
            $title = (string) $city->getDefaultTitle();
            if ($this->titles->matches($title, $needle)) {
                $suggestions[] = new PageSuggestion(
                    $title,
                    $this->paths->city((string) $city->getRegions()?->getSlug(), (string) $city->getSlug()),
                    'Település'
                );
            }
        }

        return $suggestions;
    }
}
