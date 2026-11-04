<?php

namespace App\Controller\Frontend;

use App\Search\SearchEngineInterface;
use App\Search\SearchTerm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The search page of the header form (`/search/frontend?search_form[term]=...`, the URL of the legacy site).
 */
final class SearchController extends PagedController
{
    /** The legacy search listed 12 hits on a page. */
    private const HITS_PER_PAGE = 12;

    #[Route('/search/frontend', name: 'search_frontend', methods: ['GET'])]
    public function results(Request $request, SearchTerm $terms, SearchEngineInterface $engine): Response
    {
        $term = $terms->fromRequest($request);
        $hits = null;

        if ($term !== null) {
            // The pager works on the ids of all the hits, only the hits of the shown page are loaded.
            $hits = $this->paginate($engine->findIds($term), $request, self::HITS_PER_PAGE);
            $ids = [];
            foreach ($hits as $id) {
                $ids[] = (int) $id;
            }
            $hits->setItems($engine->load($ids));
        }

        return $this->render('frontend/search/search.html.twig', ['term' => $term, 'hits' => $hits]);
    }
}
