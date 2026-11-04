<?php

namespace App\Controller\Frontend;

use App\Repository\Article\ActorRepository;
use App\Repository\Article\ActorTypeRepository;
use App\Repository\Article\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The portrait gallery: the artists, the writers and the folk artists.
 */
final class ActorController extends PagedController
{
    #[Route('/arckepcsarnok/{slug}', name: 'actor_list', methods: ['GET'])]
    public function list(
        string $slug,
        Request $request,
        ActorTypeRepository $types,
        ActorRepository $actors,
    ): Response {
        $type = $types->findOneBySlug($slug);
        if ($type === null) {
            throw $this->createNotFoundException('A keresett oldalt nem találtuk');
        }

        return $this->render('frontend/actor/actor_list.html.twig', [
            'appointments' => $this->paginate($actors->queryByType($slug), $request),
            'type' => $type,
        ]);
    }

    #[Route('/arckepcsarnok/{category}/{slug}', name: 'actor_show', methods: ['GET'])]
    public function show(
        string $category,
        string $slug,
        ActorTypeRepository $types,
        ArticleRepository $articles,
    ): Response {
        $type = $types->findOneBySlug($category);
        $actor = $articles->findActiveBySlug($slug);
        if ($type === null || $actor === null) {
            throw $this->createNotFoundException('A keresett oldalt nem találtuk');
        }

        return $this->render('frontend/actor/actor_show.html.twig', ['actor' => $actor, 'type' => $type]);
    }
}
