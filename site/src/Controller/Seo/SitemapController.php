<?php

namespace App\Controller\Seo;

use App\Seo\RobotsTxtBuilder;
use App\Seo\SeoHttpCache;
use App\Seo\SitemapBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The sitemap index (/sitemap.xml), its files (/sitemap-{name}.xml) and robots.txt. The files are at the root because
 * a sitemap may only list URLs below its own path.
 */
final class SitemapController extends AbstractController
{
    private const XML = 'application/xml; charset=UTF-8';

    public function __construct(
        private readonly SitemapBuilder $sitemapBuilder,
        private readonly RobotsTxtBuilder $robotsBuilder,
        private readonly SeoHttpCache $httpCache,
    ) {
    }

    #[Route('/sitemap.xml', name: 'seo_sitemap', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $files = $this->sitemapBuilder->files();

        return $this->httpCache->respond(
            $request,
            $this->sitemapBuilder->indexXml($files),
            self::XML,
            $this->sitemapBuilder->lastModified($files)
        );
    }

    /**
     * `articles`, or `articles-2` for the second part of a big section; only the files of the index exist.
     */
    #[Route(
        '/sitemap-{name}.xml',
        name: 'seo_sitemap_file',
        requirements: ['name' => '[a-z][a-z0-9]*(-[1-9][0-9]*)?'],
        methods: ['GET'],
    )]
    public function sitemapFile(string $name, Request $request): Response
    {
        foreach ($this->sitemapBuilder->files() as $file) {
            if ($file->name === $name) {
                return $this->httpCache->respond(
                    $request,
                    $this->sitemapBuilder->fileXml($file),
                    self::XML,
                    $file->lastModified()
                );
            }
        }

        throw $this->createNotFoundException('Sitemap file not found.');
    }

    #[Route('/robots.txt', name: 'seo_robots', methods: ['GET'])]
    public function robots(Request $request): Response
    {
        return $this->httpCache->respond($request, $this->robotsBuilder->build(), 'text/plain; charset=UTF-8', null);
    }
}
