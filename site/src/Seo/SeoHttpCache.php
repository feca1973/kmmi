<?php

namespace App\Seo;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cache headers of the generated files (sitemap, llms.txt, Markdown pages): the validators let crawlers ask
 * "not modified" (304) instead of downloading the whole file again.
 */
class SeoHttpCache
{
    private const MAX_AGE = 300;
    private const SHARED_MAX_AGE = 900;

    /**
     * Without a last modification (null) only the ETag validates the response.
     */
    public function respond(Request $request, string $content, string $contentType, ?int $lastModified): Response
    {
        $response = new Response($content, Response::HTTP_OK, ['Content-Type' => $contentType]);
        $response->setPublic();
        $response->setMaxAge(self::MAX_AGE);
        $response->setSharedMaxAge(self::SHARED_MAX_AGE);
        $response->setEtag(sha1($content));
        if ($lastModified !== null) {
            $response->setLastModified(new DateTimeImmutable('@' . $lastModified));
        }
        $response->isNotModified($request);

        return $response;
    }
}
