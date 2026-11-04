<?php

namespace App\Controller\Frontend;

use App\Repository\Article\ArticleRepository;
use App\Repository\Article\BookCategoryRepository;
use App\Repository\Article\LibraryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LibraryController extends PagedController
{
    /**
     * `/konyvtar/konyvek`, or `/konyvtar/folyoiratok/egyutt` for a category with a parent (the last one is listed).
     */
    #[Route('/konyvtar/{slug}/{subSlug}', name: 'library_list', defaults: ['subSlug' => null], methods: ['GET'])]
    public function list(
        string $slug,
        ?string $subSlug,
        Request $request,
        BookCategoryRepository $categories,
        LibraryRepository $library,
    ): Response {
        $categorySlug = $subSlug ?? $slug;
        $category = $categories->findOneBySlug($categorySlug);
        if ($category === null) {
            throw $this->createNotFoundException('A keresett oldalt nem találtuk');
        }

        return $this->render('frontend/book/book_list.html.twig', [
            'appointments' => $this->paginate($library->queryItems($categorySlug), $request),
            'type' => $category,
        ]);
    }

    #[Route('/konyvtar/{category}/reszletes/{slug}', name: 'library_show', methods: ['GET'])]
    public function show(
        string $category,
        string $slug,
        BookCategoryRepository $categories,
        ArticleRepository $articles,
    ): Response {
        $type = $categories->findOneBySlug($category);
        $book = $articles->findActiveBySlug($slug);
        if ($type === null || $book === null) {
            throw $this->createNotFoundException('A keresett oldalt nem találtuk');
        }

        return $this->render('frontend/book/book_show.html.twig', ['book' => $book, 'type' => $type]);
    }
}
