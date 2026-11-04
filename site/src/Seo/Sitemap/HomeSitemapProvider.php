<?php

namespace App\Seo\Sitemap;

use App\Seo\SitemapEntry;

/**
 * The home page (the sitemap builder dates it by the newest content).
 */
final class HomeSitemapProvider extends AbstractSitemapProvider
{
    public function section(): string
    {
        return 'pages';
    }

    public function entries(): iterable
    {
        yield new SitemapEntry('/');
    }
}
