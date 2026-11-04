<?php

namespace App\Controller\Frontend;

use App\Repository\Doc\DocRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The information pages (About us, the departments, the contacts, the partners ...).
 */
final class DocumentController extends AbstractController
{
    #[Route('/informacio/{slug}', name: 'document_single', methods: ['GET'])]
    public function show(string $slug, DocRepository $docs): Response
    {
        $doc = $docs->findActiveBySlug($slug);
        if ($doc === null) {
            throw $this->createNotFoundException('A keresett oldalt nem találtuk');
        }

        return $this->render('frontend/document/show.html.twig', ['doc' => $doc]);
    }
}
