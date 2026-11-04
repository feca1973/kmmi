<?php

namespace App\Entity\Event;

use App\Repository\Event\EventTypeRepository;
use Doctrine\DBAL\Types\Types;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\NameableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * EventType
 */
#[ORM\Table(name: 'event_type')]
#[ORM\Entity(repositoryClass: EventTypeRepository::class)]
class EventType
{
    use IdTrait;
    use NameableTrait;

    #[Gedmo\Slug(fields: ['name'])]
    #[ORM\Column(name: 'slug', type: Types::STRING, length: 255, nullable: false)]
    private ?string $slug = null;

    /**
     * @var Collection<int, Events>
     */
    #[ORM\OneToMany(targetEntity: Events::class, mappedBy: 'type', orphanRemoval: true)]
    private Collection $events;

    public function __toString(): string
    {
        return (string) $this->getDefaultName();
    }

    public function getDefaultName(): ?string
    {
        return $this->getName();
    }

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function setSlug(?string $slug): ?EventType
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @return Collection<int, Events>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Events $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setType($this);
        }

        return $this;
    }

    public function removeEvent(Events $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            // set the owning side to null (unless already changed)
            if ($event->getType() === $this) {
                $event->setType(null);
            }
        }

        return $this;
    }
}
