<?php

namespace App\Twig\Components;

use App\Entity\Event\EventType;
use App\Repository\Event\EventTypeRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * The event types (with the number of their events) in the sidebar of the event list.
 */
#[AsTwigComponent]
class EventTypes
{
    public function __construct(private readonly EventTypeRepository $types)
    {
    }

    /**
     * @return list<array{type: EventType, events: int}>
     */
    public function getTypes(): array
    {
        return $this->types->findUsedWithEventCount();
    }
}
