<?php

namespace App\Entity\Organization;

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
#[ORM\Table(name: 'organizations_translation')]
#[ORM\UniqueConstraint(columns: ['translatable_id', 'locale'])]
#[ORM\Entity]
class OrganizationTranslation
{
    use TranslationTrait;
    use TitleableTrait;

    #[ORM\ManyToOne(targetEntity: Organization::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'translatable_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?object $translatable = null;

    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(name: 'slug', type: Types::STRING, length: 255, nullable: false)]
    private ?string $slug = null;

    #[ORM\Column(name: '`lead`', type: Types::TEXT, nullable: true)]
    private ?string $lead = null;

    #[ORM\Column(name: 'content', type: Types::TEXT, nullable: true)]
    private ?string $content = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $titleOther = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $placeAddress = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $officialPersonName = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $officialPersonFunction = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $contactPersonName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $fieldOfActivityId = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $goal = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $services = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $introduction = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $officialPersonShortCv = null;

    public function __toString(): string
    {
        return $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setSlug(?string $slug): ?OrganizationTranslation
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setLead(?string $lead): OrganizationTranslation
    {
        $this->lead = $lead;

        return $this;
    }

    public function getLead(): ?string
    {
        return $this->lead;
    }

    public function setContent(?string $content): OrganizationTranslation
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getTitleOther(): ?string
    {
        return $this->titleOther;
    }

    public function setTitleOther(?string $titleOther): self
    {
        $this->titleOther = $titleOther;

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

    public function getOfficialPersonName(): ?string
    {
        return $this->officialPersonName;
    }

    public function setOfficialPersonName(?string $officialPersonName): self
    {
        $this->officialPersonName = $officialPersonName;

        return $this;
    }

    public function getOfficialPersonFunction(): ?string
    {
        return $this->officialPersonFunction;
    }

    public function setOfficialPersonFunction(?string $officialPersonFunction): self
    {
        $this->officialPersonFunction = $officialPersonFunction;

        return $this;
    }

    public function getContactPersonName(): ?string
    {
        return $this->contactPersonName;
    }

    public function setContactPersonName(?string $contactPersonName): self
    {
        $this->contactPersonName = $contactPersonName;

        return $this;
    }

    public function getFieldOfActivityId(): ?string
    {
        return $this->fieldOfActivityId;
    }

    public function setFieldOfActivityId(?string $fieldOfActivityId): self
    {
        $this->fieldOfActivityId = $fieldOfActivityId;

        return $this;
    }

    public function getGoal(): ?string
    {
        return $this->goal;
    }

    public function setGoal(?string $goal): self
    {
        $this->goal = $goal;

        return $this;
    }

    public function getServices(): ?string
    {
        return $this->services;
    }

    public function setServices(?string $services): self
    {
        $this->services = $services;

        return $this;
    }

    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    public function setIntroduction(?string $introduction): self
    {
        $this->introduction = $introduction;

        return $this;
    }

    public function getOfficialPersonShortCv(): ?string
    {
        return $this->officialPersonShortCv;
    }

    public function setOfficialPersonShortCv(?string $officialPersonShortCv): self
    {
        $this->officialPersonShortCv = $officialPersonShortCv;

        return $this;
    }
}
