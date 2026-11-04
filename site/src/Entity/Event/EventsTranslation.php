<?php

namespace App\Entity\Event;

use Doctrine\DBAL\Types\Types;
use App\Entity\Traits\TranslationTrait;
use App\Entity\Traits\TitleableTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Doctrine entity: the `translatable` association is read through TranslationTrait.
 *
 * @SuppressWarnings("PHPMD.UnusedPrivateField")
 */
#[ORM\Table(name: 'events_translation')]
#[ORM\UniqueConstraint(columns: ['translatable_id', 'locale'])]
#[ORM\Entity]
class EventsTranslation
{
    use TranslationTrait;
    use TitleableTrait;

    #[ORM\ManyToOne(targetEntity: Events::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'translatable_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?object $translatable = null;

    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(name: 'slug', type: Types::STRING, length: 255, nullable: false)]
    private ?string $slug = null;

    #[ORM\Column(name: '`lead`', type: Types::TEXT, nullable: true)]
    private ?string $lead = null;

    #[ORM\Column(name: 'content', type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(name: 'place_name', type: Types::STRING, length: 255, nullable: true)]
    private ?string $placeName = null;

    #[ORM\Column(name: 'place_address', type: Types::STRING, length: 255, nullable: true)]
    private ?string $placeAddress = null;

    #[ORM\Column(name: 'contact_person', type: Types::STRING, length: 255, nullable: true)]
    private ?string $contactPerson = null;


    public function __toString(): string
    {
        return $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function setSlug(?string $slug): ?EventsTranslation
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setLead(?string $lead): EventsTranslation
    {
        $this->lead = $lead;

        return $this;
    }

    public function getLead(): ?string
    {
        return $this->lead;
    }

    public function setContent(?string $content): EventsTranslation
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getPlaceName(): ?string
    {
        return $this->placeName;
    }

    public function setPlaceName(?string $placeName): self
    {
        $this->placeName = $placeName;

        return $this;
    }

    public function getPlaceAddress(): ?string
    {
        return $this->placeAddress;
    }

    public function setPlaceAddress(?string $placeAddress): self
    {
        $this->placeAddress = $placeAddress;

        return $this;
    }

    public function getContactPerson(): ?string
    {
        return $this->contactPerson;
    }

    public function setContactPerson(?string $contactPerson): self
    {
        $this->contactPerson = $contactPerson;

        return $this;
    }
}
