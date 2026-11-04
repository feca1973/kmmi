<?php

namespace App\Twig\Components;

use App\Entity\Article\Article;
use App\Entity\Article\ArticleType;
use App\Repository\Article\ArticleRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * The latest news of the homepage (two columns) and of the sidebars (one column).
 */
#[AsTwigComponent]
class SidebarNews
{
    public int $size = 6;

    public int $columns = 1;

    public function __construct(private readonly ArticleRepository $articles)
    {
    }

    /**
     * @return list<Article>
     */
    public function getArticles(): array
    {
        return $this->articles->findLatest(ArticleType::NEWS_ID, $this->size);
    }
}
