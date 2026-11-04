<?php

namespace App\Seo\Sitemap;

use App\Enum\MediaType;
use App\Repository\MediaRepository;
use App\Seo\SitemapEntry;

/**
 * The audio and video libraries and their items.
 */
final class MediaSitemapProvider extends AbstractSitemapProvider
{
    public function __construct(private readonly MediaRepository $media)
    {
    }

    public function section(): string
    {
        return 'media';
    }

    public function entries(): iterable
    {
        $items = $this->media->sitemapRows();
        $types = array_unique(array_map(static fn (array $item): MediaType => $item['type'], $items), SORT_REGULAR);

        foreach ($types as $type) {
            yield new SitemapEntry($this->base($type));
        }
        foreach ($items as $item) {
            yield new SitemapEntry($this->base($item['type']) . '/' . $item['slug']);
        }
    }

    private function base(MediaType $type): string
    {
        return $type === MediaType::Audio ? '/hangtar' : '/videotar';
    }
}
