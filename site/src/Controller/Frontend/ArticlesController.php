<?php

namespace App\Controller\Frontend;

use App\Entity\Article\Article;
use App\Entity\Article\ArticleType;
use App\Repository\Article\ArticleRepository;
use App\Repository\Article\ArticleTypeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ArticlesController extends PagedController
{
    #[Route('/cikkek/{slug}', name: 'news_list', methods: ['GET'])]
    public function newsList(
        string $slug,
        Request $request,
        ArticleTypeRepository $types,
        ArticleRepository $articles,
    ): Response {
        $type = $types->findOneBySlug($slug);
        if ($type === null) {
            throw $this->createNotFoundException('A keresett oldalt nem találtuk');
        }

        return $this->render('frontend/article/article_list.html.twig', [
            'appointments' => $this->paginate($articles->queryPublishedByTypeSlug($slug), $request),
            'type' => $type,
        ]);
    }

    #[Route('/cikkek/{type}/{slug}', name: 'news_single', methods: ['GET'])]
    public function newsShow(string $slug, ArticleRepository $articles): Response
    {
        return $this->showArticle($articles->findActiveBySlug($slug));
    }

    #[Route('/egyhazak', name: 'churches_list', methods: ['GET'])]
    public function churches(Request $request, ArticleRepository $articles): Response
    {
        return $this->renderTypeList(
            'frontend/article/church_list.html.twig',
            ArticleType::CHURCHES_ID,
            $request,
            $articles
        );
    }

    #[Route('/nepmuveszet', name: 'folk_list', methods: ['GET'])]
    public function folkList(Request $request, ArticleRepository $articles): Response
    {
        return $this->renderTypeList(
            'frontend/article/folk_list.html.twig',
            ArticleType::FOLK_ART_ID,
            $request,
            $articles
        );
    }

    #[Route('/nepmuveszet/{slug}', name: 'folk_show', methods: ['GET'])]
    public function folkShow(string $slug, ArticleRepository $articles): Response
    {
        return $this->showArticle($articles->findActiveBySlug($slug));
    }

    private function showArticle(?Article $article): Response
    {
        if ($article === null) {
            throw $this->createNotFoundException('A keresett oldalt nem találtuk');
        }

        return $this->render('frontend/article/article_show.html.twig', ['article' => $article, 'city' => false]);
    }

    private function renderTypeList(
        string $template,
        int $typeId,
        Request $request,
        ArticleRepository $articles,
    ): Response {
        $appointments = $this->paginate($articles->queryByTypeId($typeId), $request);
        if (count($appointments) === 0) {
            throw $this->createNotFoundException('A keresett oldalt nem találtuk, vagy nincs kapcsolódó tartalom');
        }

        return $this->render($template, ['appointments' => $appointments]);
    }
}
