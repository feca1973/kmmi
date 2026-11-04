<?php

namespace App\Controller\Frontend;

use App\Repository\Article\EducationRepository;
use App\Repository\Article\EducationTypeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EducationController extends PagedController
{
    private const INSTITUTIONS_PER_PAGE = 13;

    #[Route('/oktatasi-intezmenyek/{slug}', name: 'education_list', methods: ['GET'])]
    public function list(
        string $slug,
        Request $request,
        EducationTypeRepository $types,
        EducationRepository $education,
    ): Response {
        $type = $types->findOneBySlug($slug);
        if ($type === null) {
            throw $this->createNotFoundException('A keresett oldalt nem találtuk');
        }

        return $this->render('frontend/education/education_list.html.twig', [
            'appointments' => $this->paginate(
                $education->queryByType($slug),
                $request,
                self::INSTITUTIONS_PER_PAGE
            ),
            'type' => $type,
        ]);
    }
}
