<?php

namespace App\Seo\Llms;

use App\Entity\Article\Article;
use App\Entity\Article\ArticleType;
use App\Repository\Article\ArticleRepository;
use App\Repository\Doc\DocRepository;
use App\Repository\Event\EventsRepository;
use App\Repository\Monuments\MonumentRepository;
use App\Repository\Organization\OrganizationRepository;
use App\Repository\Region\CitiesRepository;

/**
 * The permanent content of the site as Markdown pages (for the llms-full.txt), by categories; and the latest news.
 * The library items are catalog entries here (their text is on their own Markdown pages).
 *
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
class ContentPages
{
    public function __construct(
        private readonly PageKnowledge $knowledge,
        private readonly ArticleRepository $articles,
        private readonly DocRepository $docs,
        private readonly EventsRepository $events,
        private readonly MonumentRepository $monuments,
        private readonly OrganizationRepository $organizations,
        private readonly CitiesRepository $cities,
    ) {
    }

    /**
     * @return array<string, list<MarkdownPage>> the title of the category => its pages
     */
    public function categories(): array
    {
        return [
            'Információs oldalak' => array_map($this->knowledge->document(...), $this->docs->findAllActive()),
            'Szervezetek' => array_map($this->knowledge->organization(...), $this->organizations->findAllActive()),
            'Települések' => array_map($this->knowledge->city(...), $this->cities->findAllActive()),
            'Emlékhelyek' => array_map($this->knowledge->monument(...), $this->monuments->findAllActive()),
            'Arcképcsarnok' => array_map(
                $this->knowledge->actor(...),
                $this->articles->findAllOfType('actorType')
            ),
            'Oktatási intézmények' => array_map(
                $this->knowledge->education(...),
                $this->articles->findAllOfType('educationType')
            ),
            'Könyvtár' => array_map(
                fn (Article $book): MarkdownPage => $this->knowledge->libraryItem($book, false),
                $this->articles->findAllOfType('bookCategory')
            ),
            'Rendezvények' => array_map($this->knowledge->event(...), $this->events->findAllActive()),
        ];
    }

    /**
     * @return list<MarkdownPage>
     */
    public function latestNews(int $limit): array
    {
        return array_map(
            $this->knowledge->article(...),
            $this->articles->findLatest(ArticleType::NEWS_ID, $limit)
        );
    }
}
