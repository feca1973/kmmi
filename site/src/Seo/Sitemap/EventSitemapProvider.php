<?php

namespace App\Seo\Sitemap;

use App\Repository\Event\EventsRepository;
use App\Repository\Event\EventTypeRepository;
use App\Seo\PublicPaths;
use App\Seo\SitemapEntry;

/**
 * The event list, the lists of the event types and the events.
 */
final class EventSitemapProvider extends AbstractSitemapProvider
{
    public function __construct(
        private readonly EventsRepository $events,
        private readonly EventTypeRepository $types,
        private readonly PublicPaths $paths,
    ) {
    }

    public function section(): string
    {
        return 'events';
    }

    public function entries(): iterable
    {
        $types = $this->types->sitemapRows();
        if ($types !== []) {
            yield new SitemapEntry('/rendezvenyek');
        }
        foreach ($types as $type) {
            yield new SitemapEntry('/rendezvenyek/' . $type['slug'], $this->timestamp($type['updated']));
        }

        foreach ($this->events->sitemapRows() as $event) {
            yield new SitemapEntry(
                $this->paths->event($event['typeSlug'], $event['slug']),
                $this->timestamp($event['updated'])
            );
        }
    }
}
