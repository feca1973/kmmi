<?php

namespace App\Menu\Source;

use App\Entity\Article\ArticleType;
use App\Menu\PageSourceInterface;
use App\Menu\PageSuggestion;
use App\Menu\TitleMatcher;
use App\Repository\Article\ArticleTypeRepository;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * The lists of the article groups (/cikkek/{group}). The folk art and the churches have their own lists, among the
 * main lists.
 */
#[AsTaggedItem(priority: 50)]
final class ArticleGroupPageSource implements PageSourceInterface
{
    public function __construct(
        private readonly TitleMatcher $titles,
        private readonly ArticleTypeRepository $articleTypes,
        private readonly UrlGeneratorInterface $router,
    ) {
    }

    public function find(string $term): array
    {
        $needle = $this->titles->needle($term);
        $own = [ArticleType::FOLK_ART_ID, ArticleType::CHURCHES_ID];
        $suggestions = [];
        foreach ($this->articleTypes->findAllWithTranslations() as $type) {
            $title = (string) $type->getDefaultName();
            if (in_array($type->getId(), $own, true) || !$this->titles->matches($title, $needle)) {
                continue;
            }
            $path = $this->router->generate('news_list', ['slug' => (string) $type->getSlug()]);
            $suggestions[] = new PageSuggestion($title, $path, 'Cikkcsoport');
        }

        return $suggestions;
    }
}
