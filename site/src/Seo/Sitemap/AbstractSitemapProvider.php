<?php

namespace App\Seo\Sitemap;

use App\Seo\SitemapProviderInterface;
use DateTimeImmutable;
use DateTimeInterface;

abstract class AbstractSitemapProvider implements SitemapProviderInterface
{
    /**
     * The unix timestamp of a date column of a scalar query: a date, or a string for an aggregate (MAX(...)).
     */
    protected function timestamp(DateTimeInterface|string|null $date): ?int
    {
        if ($date === null) {
            return null;
        }

        return ($date instanceof DateTimeInterface ? $date : new DateTimeImmutable($date))->getTimestamp();
    }
}
