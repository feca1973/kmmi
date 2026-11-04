<?php

namespace App\Controller\Seo;

use App\Seo\Llms\LlmsBuilder;
use App\Seo\SeoHttpCache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The files for the language models: /llms.txt and /llms-full.txt.
 */
final class LlmsController extends AbstractController
{
    private const TEXT = 'text/plain; charset=UTF-8';

    public function __construct(
        private readonly LlmsBuilder $builder,
        private readonly SeoHttpCache $httpCache,
    ) {
    }

    #[Route('/llms.txt', name: 'seo_llms', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->httpCache->respond($request, $this->builder->index(), self::TEXT, null);
    }

    #[Route('/llms-full.txt', name: 'seo_llms_full', methods: ['GET'])]
    public function full(Request $request): Response
    {
        return $this->httpCache->respond($request, $this->builder->full(), self::TEXT, null);
    }
}
