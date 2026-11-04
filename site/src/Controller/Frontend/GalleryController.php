<?php

namespace App\Controller\Frontend;

use App\Repository\Gallery\GalleryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GalleryController extends PagedController
{
    private const GALLERIES_PER_PAGE = 30;

    #[Route('/kepek', name: 'gallery_list', methods: ['GET'])]
    public function list(Request $request, GalleryRepository $galleries): Response
    {
        return $this->render('frontend/media/gallery_list.html.twig', [
            'appointments' => $this->paginate($galleries->queryActive(), $request, self::GALLERIES_PER_PAGE),
        ]);
    }

    #[Route('/galeria/{slug}', name: 'gallery_single', methods: ['GET'])]
    public function show(string $slug, GalleryRepository $galleries): Response
    {
        $gallery = $galleries->findActiveWithImages($slug);
        if ($gallery === null) {
            throw $this->createNotFoundException('A keresett oldalt nem találtuk');
        }

        return $this->render('frontend/media/gallery_single.html.twig', ['gallery' => $gallery]);
    }
}
