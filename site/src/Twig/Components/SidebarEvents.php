<?php

namespace App\Twig\Components;

use App\Entity\Event\Events;
use App\Repository\Event\EventsRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * The latest events of the homepage and the sidebars.
 */
#[AsTwigComponent]
class SidebarEvents
{
    public int $size = 6;

    public function __construct(private readonly EventsRepository $events)
    {
    }

    /**
     * @return list<Events>
     */
    public function getEvents(): array
    {
        return $this->events->findLatest($this->size);
    }
}
