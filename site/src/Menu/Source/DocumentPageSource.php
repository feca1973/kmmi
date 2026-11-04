<?php

namespace App\Menu\Source;

use App\Menu\PageSourceInterface;
use App\Menu\PageSuggestion;
use App\Menu\TitleMatcher;
use App\Repository\Doc\DocRepository;
use App\Seo\PublicPaths;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * The information pages (/informacio/{slug}).
 */
#[AsTaggedItem(priority: 40)]
final class DocumentPageSource implements PageSourceInterface
{
    public function __construct(
        private readonly TitleMatcher $titles,
        private readonly DocRepository $docs,
        private readonly PublicPaths $paths,
    ) {
    }

    public function find(string $term): array
    {
        $needle = $this->titles->needle($term);
        $suggestions = [];
        foreach ($this->docs->findAllActive() as $doc) {
            $title = (string) $doc->getDefaultTitle();
            if ($this->titles->matches($title, $needle)) {
                $suggestions[] = new PageSuggestion(
                    $title,
                    $this->paths->document((string) $doc->getSlug()),
                    'Információs oldal'
                );
            }
        }

        return $suggestions;
    }
}
