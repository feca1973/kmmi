<?php

namespace App\Controller\Seo;

use App\Repository\Article\ArticleRepository;
use App\Repository\Doc\DocRepository;
use App\Repository\Event\EventsRepository;
use App\Repository\Monuments\MonumentRepository;
use App\Repository\Organization\OrganizationRepository;
use App\Repository\Region\CitiesRepository;
use App\Seo\Llms\MarkdownPage;
use App\Seo\Llms\MarkdownRenderer;
use App\Seo\Llms\PageKnowledge;
use App\Seo\SeoHttpCache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The Markdown version of the content pages for the language models: the URL of the page and `.md`. The routes are
 * more important than the ones of the HTML pages (the slug of those could end with ".md").
 *
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
final class MarkdownPageController extends AbstractController
{
    private const MARKDOWN = 'text/markdown; charset=UTF-8';

    public function __construct(
        private readonly PageKnowledge $knowledge,
        private readonly MarkdownRenderer $renderer,
        private readonly SeoHttpCache $httpCache,
    ) {
    }

    #[Route('/cikkek/{type}/{slug}.md', name: 'seo_article_markdown', methods: ['GET'], priority: 10)]
    public function article(string $slug, Request $request, ArticleRepository $articles): Response
    {
        $article = $articles->findActiveBySlug($slug);

        return $this->markdown($request, $article === null ? null : $this->knowledge->article($article));
    }

    #[Route('/nepmuveszet/{slug}.md', name: 'seo_folk_markdown', methods: ['GET'], priority: 10)]
    public function folkArt(string $slug, Request $request, ArticleRepository $articles): Response
    {
        $article = $articles->findActiveBySlug($slug);

        return $this->markdown($request, $article === null ? null : $this->knowledge->article($article));
    }

    #[Route('/rendezvenyek/{type}/{slug}.md', name: 'seo_event_markdown', methods: ['GET'], priority: 10)]
    public function event(string $slug, Request $request, EventsRepository $events): Response
    {
        $event = $events->findActiveBySlug($slug);

        return $this->markdown($request, $event === null ? null : $this->knowledge->event($event));
    }

    #[Route('/emlekhelyek/{type}/{slug}.md', name: 'seo_monument_markdown', methods: ['GET'], priority: 10)]
    public function monument(string $slug, Request $request, MonumentRepository $monuments): Response
    {
        $monument = $monuments->findActiveBySlug($slug);

        return $this->markdown($request, $monument === null ? null : $this->knowledge->monument($monument));
    }

    #[Route('/informacio/{slug}.md', name: 'seo_document_markdown', methods: ['GET'], priority: 10)]
    public function document(string $slug, Request $request, DocRepository $docs): Response
    {
        $doc = $docs->findActiveBySlug($slug);

        return $this->markdown($request, $doc === null ? null : $this->knowledge->document($doc));
    }

    #[Route('/konyvtar/{category}/reszletes/{slug}.md', name: 'seo_library_markdown', methods: ['GET'], priority: 10)]
    public function libraryItem(string $slug, Request $request, ArticleRepository $articles): Response
    {
        $book = $articles->findActiveBySlug($slug);

        return $this->markdown($request, $book === null ? null : $this->knowledge->libraryItem($book));
    }

    #[Route('/arckepcsarnok/{type}/{slug}.md', name: 'seo_actor_markdown', methods: ['GET'], priority: 10)]
    public function actor(string $slug, Request $request, ArticleRepository $articles): Response
    {
        $actor = $articles->findActiveBySlug($slug);

        return $this->markdown($request, $actor === null ? null : $this->knowledge->actor($actor));
    }

    #[Route('/szervezetek/{slug}.md', name: 'seo_organization_markdown', methods: ['GET'], priority: 10)]
    public function organization(string $slug, Request $request, OrganizationRepository $organizations): Response
    {
        $organization = $organizations->findActiveBySlug($slug);

        return $this->markdown($request, $organization === null ? null : $this->knowledge->organization($organization));
    }

    #[Route('/regiok/telepulesek/{region}/{slug}.md', name: 'seo_city_markdown', methods: ['GET'], priority: 10)]
    public function city(string $slug, Request $request, CitiesRepository $cities): Response
    {
        $city = $cities->findActiveBySlug($slug);

        return $this->markdown($request, $city === null ? null : $this->knowledge->city($city));
    }

    private function markdown(Request $request, ?MarkdownPage $page): Response
    {
        if ($page === null) {
            throw $this->createNotFoundException('A keresett oldalt nem találtuk');
        }

        return $this->httpCache->respond($request, $this->renderer->render($page), self::MARKDOWN, $page->lastModified);
    }
}
