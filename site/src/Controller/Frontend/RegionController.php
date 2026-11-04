<?php

namespace App\Controller\Frontend;

use App\Repository\Region\CitiesRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RegionController extends PagedController
{
    private const CITIES_PER_PAGE = 13;

    #[Route('/regiok/telepulesek', name: 'cities_list', methods: ['GET'])]
    public function list(Request $request, CitiesRepository $cities): Response
    {
        return $this->render('frontend/region/cities_list.html.twig', [
            'appointments' => $this->paginate($cities->queryActive(), $request, self::CITIES_PER_PAGE),
        ]);
    }

    #[Route('/regiok/telepulesek/{region}/{slug}', name: 'cities_single', methods: ['GET'])]
    public function show(string $slug, CitiesRepository $cities): Response
    {
        $city = $cities->findActiveBySlug($slug);
        if ($city === null) {
            throw $this->createNotFoundException('A keresett oldalt nem találtuk');
        }

        return $this->render('frontend/region/city_show.html.twig', ['city' => $city]);
    }
}
