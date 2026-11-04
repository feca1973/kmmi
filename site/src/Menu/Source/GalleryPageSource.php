<?php

namespace App\Menu\Source;

use App\Menu\PageSourceInterface;
use App\Menu\PageSuggestion;
use App\Menu\TitleMatcher;
use App\Repository\Gallery\GalleryRepository;
use App\Seo\PublicPaths;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * The galleries (/galeria/{slug}).
 */
#[AsTaggedItem(priority: 30)]
final class GalleryPageSource implements PageSourceInterface
{
    public function __construct(
        private readonly TitleMatcher $titles,
        private readonly GalleryRepository $galleries,
        private readonly PublicPaths $paths,
    ) {
    }

    public function find(string $term): array
    {
        $needle = $this->titles->needle($term);
        $suggestions = [];
        foreach ($this->galleries->findAllPublic() as $gallery) {
            $name = (string) $gallery->getName();
            if ($this->titles->matches($name, $needle)) {
                $suggestions[] = new PageSuggestion(
                    $name,
                    $this->paths->gallery((string) $gallery->getSlug()),
                    'Galéria'
                );
            }
        }

        return $suggestions;
    }
}
