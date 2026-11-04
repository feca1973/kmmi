<?php

namespace App\Controller\Frontend;

use App\Repository\Monuments\MonumentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MonumentController extends PagedController
{
    #[Route('/emlekhelyek', name: 'monuments_list', methods: ['GET'])]
    public function monumentsList(Request $request, MonumentRepository $monuments): Response
    {
        $appointments = $this->paginate($monuments->queryActive(), $request);
        if (count($appointments) === 0) {
            throw $this->createNotFoundException('A keresett oldalt nem találtuk, vagy nincs kapcsolódó tartalom');
        }

        return $this->render('frontend/monument/list.html.twig', ['appointments' => $appointments]);
    }

    #[Route('/emlekhelyek/{type}/{slug}', name: 'monument_show', methods: ['GET'])]
    public function monumentShow(string $slug, MonumentRepository $monuments): Response
    {
        $monument = $monuments->findActiveBySlug($slug);
        if ($monument === null) {
            throw $this->createNotFoundException('A keresett oldalt nem találtuk');
        }

        return $this->render('frontend/monument/show.html.twig', ['monument' => $monument]);
    }
}
