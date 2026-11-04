<?php

namespace App\Seo;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * A part of the site adds its public URLs to the sitemap by implementing this interface: the class is found by its
 * tag, no other registration is needed. Every `section()` gets its own `/sitemap-{section}.xml` file, listed in the
 * sitemap index automatically.
 */
#[AutoconfigureTag('app.sitemap_provider')]
interface SitemapProviderInterface
{
    /**
     * The sitemap file the URLs go to: `/sitemap-{section}.xml`. Lowercase letters and digits, starting with a letter
     * (`SitemapBuilder::SECTION_PATTERN`). Providers of the same section share a file; a file is split at 50 000 URLs.
     */
    public function section(): string;

    /**
     * The URLs of the pages that exist (a URL of the sitemap that is not a 200 page is a bug). The queries are the
     * ones of the pages, scalar: the sitemap is built per request.
     *
     * @return iterable<SitemapEntry>
     */
    public function entries(): iterable;
}
