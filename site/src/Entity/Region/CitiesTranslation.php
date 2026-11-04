<?php

namespace App\Entity\Region;

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
#[ORM\Table(name: 'cities_translation')]
#[ORM\UniqueConstraint(columns: ['translatable_id', 'locale'])]
#[ORM\Entity]
class CitiesTranslation
{
    use TranslationTrait;
    use TitleableTrait;

    #[ORM\ManyToOne(targetEntity: Cities::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'translatable_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?object $translatable = null;

    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(name: 'slug', type: Types::STRING, length: 255, nullable: false)]
    private ?string $slug = null;

    #[ORM\Column(name: '`lead`', type: Types::TEXT, nullable: true)]
    private ?string $lead = null;

    #[ORM\Column(name: 'content', type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    public function __toString(): string
    {
        return $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function setSlug(?string $slug): ?CitiesTranslation
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setLead(?string $lead): CitiesTranslation
    {
        $this->lead = $lead;

        return $this;
    }

    public function getLead(): ?string
    {
        return $this->lead;
    }

    public function setContent(?string $content): CitiesTranslation
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }
}
