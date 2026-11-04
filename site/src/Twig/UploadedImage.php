<?php

namespace App\Twig;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Twig\Attribute\AsTwigFilter;

/**
 * The URLs of the uploaded images in the size and format of a Liip Imagine filter set
 * (config/packages/liip_imagine.yaml). The public pages never link the uploaded original (see AGENTS.md).
 *
 * `{{ article.mainImage|uploaded_image('content800') }}`
 * `srcset="{{ article.mainImage|uploaded_srcset('article') }}"`
 */
class UploadedImage
{
    /**
     * The filter sets (and the widths of their images) offered to the browser for a kind of image.
     */
    private const SRCSETS = [
        'list' => ['thumb160' => 160, 'card345' => 345, 'content500' => 500],
        'article' => ['content500' => 500, 'content800' => 800, 'content1200' => 1200],
    ];

    public function __construct(private readonly CacheManager $cacheManager)
    {
    }

    #[AsTwigFilter('uploaded_image')]
    public function url(string $path, string $filter): string
    {
        // The stored paths start with "/" and may be URL encoded (/uploads/images/a%20b.jpg).
        return $this->cacheManager->getBrowserPath(rawurldecode(ltrim($path, '/')), $filter);
    }

    #[AsTwigFilter('uploaded_srcset')]
    public function srcset(string $path, string $kind): string
    {
        $candidates = [];
        foreach (self::SRCSETS[$kind] as $filter => $width) {
            $candidates[] = $this->url($path, $filter) . ' ' . $width . 'w';
        }

        return implode(', ', $candidates);
    }
}
