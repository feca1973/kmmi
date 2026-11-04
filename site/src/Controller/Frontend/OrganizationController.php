<?php

namespace App\Controller\Frontend;

use App\Repository\Organization\OrganizationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OrganizationController extends PagedController
{
    private const ORGANIZATIONS_PER_PAGE = 13;

    #[Route('/szervezetek', name: 'organization_list', methods: ['GET'])]
    public function list(Request $request, OrganizationRepository $organizations): Response
    {
        return $this->render('frontend/organization/organization_list.html.twig', [
            'appointments' => $this->paginate($organizations->queryActive(), $request, self::ORGANIZATIONS_PER_PAGE),
        ]);
    }

    #[Route('/szervezetek/{slug}', name: 'organization_single', methods: ['GET'])]
    public function show(string $slug, OrganizationRepository $organizations): Response
    {
        $organization = $organizations->findActiveBySlug($slug);
        if ($organization === null) {
            throw $this->createNotFoundException('A keresett oldalt nem találtuk');
        }

        return $this->render('frontend/organization/organization_show.html.twig', ['organization' => $organization]);
    }
}
