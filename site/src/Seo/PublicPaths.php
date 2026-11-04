<?php

namespace App\Seo;

use App\Entity\Article\ArticleType;

/**
 * The paths of the detail pages of the content, built from the slugs (and so from scalar query results too). The paths
 * are the URLs of the live site (see AGENTS.md); this is their only definition for the code that does not render a
 * template (sitemap, Markdown pages), PublicPathsTest checks them against the routes of the controllers.
 */
class PublicPaths
{
    /**
     * The page of an article: the folk art has its own URLs, the others are under their article type.
     */
    public function article(int $typeId, string $typeSlug, string $slug): string
    {
        return $typeId === ArticleType::FOLK_ART_ID ? '/nepmuveszet/' . $slug : '/cikkek/' . $typeSlug . '/' . $slug;
    }

    public function libraryItem(string $categorySlug, string $slug): string
    {
        return '/konyvtar/' . $categorySlug . '/reszletes/' . $slug;
    }

    public function actor(string $typeSlug, string $slug): string
    {
        return '/arckepcsarnok/' . $typeSlug . '/' . $slug;
    }

    public function event(string $typeSlug, string $slug): string
    {
        return '/rendezvenyek/' . $typeSlug . '/' . $slug;
    }

    public function monument(string $typeSlug, string $slug): string
    {
        return '/emlekhelyek/' . $typeSlug . '/' . $slug;
    }

    public function organization(string $slug): string
    {
        return '/szervezetek/' . $slug;
    }

    public function city(string $regionSlug, string $slug): string
    {
        return '/regiok/telepulesek/' . $regionSlug . '/' . $slug;
    }

    public function document(string $slug): string
    {
        return '/informacio/' . $slug;
    }

    public function gallery(string $slug): string
    {
        return '/galeria/' . $slug;
    }
}
