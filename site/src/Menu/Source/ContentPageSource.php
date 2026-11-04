<?php

namespace App\Menu\Source;

use App\Menu\PageSourceInterface;
use App\Menu\PageSuggester;
use App\Menu\PageSuggestion;
use App\Search\SearchEngineInterface;
use App\Search\SearchEntryFactory;
use App\Search\SearchHit;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * The articles, events, monuments and organizations the search finds (the same index as the public search).
 */
#[AsTaggedItem(priority: 10)]
final class ContentPageSource implements PageSourceInterface
{
    /**
     * @var array<string, string>
     */
    private const KINDS = [
        SearchEntryFactory::ARTICLE => 'Cikk',
        SearchEntryFactory::EVENT => 'Esemény',
        SearchEntryFactory::MONUMENT => 'Emlékhely',
        SearchEntryFactory::ORGANIZATION => 'Szervezet',
    ];

    public function __construct(private readonly SearchEngineInterface $search)
    {
    }

    public function find(string $term): array
    {
        $hits = $this->search->load(array_slice($this->search->findIds($term), 0, PageSuggester::LIMIT));

        return array_map(
            static fn (SearchHit $hit): PageSuggestion => new PageSuggestion(
                $hit->title,
                $hit->url,
                self::KINDS[$hit->type] ?? 'Oldal'
            ),
            $hits
        );
    }
}
