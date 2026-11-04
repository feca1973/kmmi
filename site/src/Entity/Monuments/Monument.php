<?php

namespace App\Entity\Monuments;

use App\Repository\Monuments\MonumentRepository;
use Doctrine\DBAL\Types\Types;
use DateTimeInterface;
use App\Entity\Gallery\Gallery;
use App\Entity\Monuments\MonumentExisting;
use App\Entity\Monuments\MonumentType;
use App\Entity\Monuments\MonumentLocalization;
use App\Entity\Monuments\MonumentSubmitter;
use App\Entity\Traits\ActivableTrait;
use App\Entity\Traits\BlameableTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\ImageableTrait;
use App\Entity\Traits\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Maps the legacy `monuments` table, which has this many columns.
 *
 * @SuppressWarnings("PHPMD.TooManyFields")
 * @SuppressWarnings("PHPMD.ExcessivePublicCount")
 */
#[ORM\Table(name: 'monuments')]
#[ORM\Entity(repositoryClass: MonumentRepository::class)]
class Monument
{
    use IdTrait;
    use BlameableTrait;
    use TimestampableTrait;
    use ActivableTrait;
    use ImageableTrait;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: MonumentType::class, inversedBy: 'monuments')]
    private ?MonumentType $type = null;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: MonumentExisting::class, inversedBy: 'monuments')]
    private ?MonumentExisting $existing = null;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: MonumentLocalization::class, inversedBy: 'monuments')]
    private ?MonumentLocalization $localization = null;

    #[ORM\ManyToOne(targetEntity: MonumentSubmitter::class, inversedBy: 'monuments')]
    private ?MonumentSubmitter $submitter = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $name = null;

    #[Gedmo\Slug(fields: ['name'])]
    #[ORM\Column(name: 'slug', type: Types::STRING, length: 255, nullable: false)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $placeCity = null;

    #[ORM\Column(type: Types::STRING, length: 512, nullable: true)]
    private ?string $placeAddress = null;

    #[ORM\Column(type: Types::SMALLFLOAT, precision: 11, scale: 8, nullable: true)]
    private ?float $lat = null;

    #[ORM\Column(type: Types::SMALLFLOAT, precision: 11, scale: 8, nullable: true)]
    private ?float $lng = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $createdDate = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $creatorName = null;

    #[ORM\Column(type: Types::STRING, length: 512, nullable: true)]
    private ?string $submitterPurpose = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $monumentCaption = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $usageDate = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $caringBy = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $momumentCondition = null;

    #[ORM\Column(type: Types::STRING, length: 512, nullable: true)]
    private ?string $relatedEvents = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $longDescription = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $sourceInfo = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $compiledBy = '';

    #[ORM\ManyToOne(targetEntity: Gallery::class, inversedBy: 'monuments')]
    private ?Gallery $gallery = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $photoBy = null;

    public function __toString(): string
    {
        return (string) $this->getName();
    }


    public function getType(): ?MonumentType
    {
        return $this->type;
    }

    public function setType(?MonumentType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getExisting(): ?MonumentExisting
    {
        return $this->existing;
    }

    public function setExisting(?MonumentExisting $existing): self
    {
        $this->existing = $existing;

        return $this;
    }

    public function getLocalization(): ?MonumentLocalization
    {
        return $this->localization;
    }

    public function setLocalization(?MonumentLocalization $localization): self
    {
        $this->localization = $localization;

        return $this;
    }

    public function getSubmitter(): ?MonumentSubmitter
    {
        return $this->submitter;
    }

    public function setSubmitter(?MonumentSubmitter $submitter): self
    {
        $this->submitter = $submitter;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getPlaceCity(): ?string
    {
        return $this->placeCity;
    }

    public function setPlaceCity(string $placeCity): self
    {
        $this->placeCity = $placeCity;

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

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(?float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLng(): ?float
    {
        return $this->lng;
    }

    public function setLng(?float $lng): self
    {
        $this->lng = $lng;

        return $this;
    }

    public function getCreatedDate(): ?DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(?DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getCreatorName(): ?string
    {
        return $this->creatorName;
    }

    public function setCreatorName(string $creatorName): self
    {
        $this->creatorName = $creatorName;

        return $this;
    }

    public function getSubmitterPurpose(): ?string
    {
        return $this->submitterPurpose;
    }

    public function setSubmitterPurpose(?string $submitterPurpose): self
    {
        $this->submitterPurpose = $submitterPurpose;

        return $this;
    }

    public function getMonumentCaption(): ?string
    {
        return $this->monumentCaption;
    }

    public function setMonumentCaption(?string $monumentCaption): self
    {
        $this->monumentCaption = $monumentCaption;

        return $this;
    }

    public function getUsageDate(): ?string
    {
        return $this->usageDate;
    }

    public function setUsageDate(?string $usageDate): self
    {
        $this->usageDate = $usageDate;

        return $this;
    }

    public function getCaringBy(): ?string
    {
        return $this->caringBy;
    }

    public function setCaringBy(?string $caringBy): self
    {
        $this->caringBy = $caringBy;

        return $this;
    }

    public function getMomumentCondition(): ?string
    {
        return $this->momumentCondition;
    }

    public function setMomumentCondition(string $momumentCondition): self
    {
        $this->momumentCondition = $momumentCondition;

        return $this;
    }

    public function getRelatedEvents(): ?string
    {
        return $this->relatedEvents;
    }

    public function setRelatedEvents(?string $relatedEvents): self
    {
        $this->relatedEvents = $relatedEvents;

        return $this;
    }

    public function getLongDescription(): ?string
    {
        return $this->longDescription;
    }

    public function setLongDescription(string $longDescription): self
    {
        $this->longDescription = $longDescription;

        return $this;
    }

    public function getSourceInfo(): ?string
    {
        return $this->sourceInfo;
    }

    public function setSourceInfo(?string $sourceInfo): self
    {
        $this->sourceInfo = $sourceInfo;

        return $this;
    }

    public function getCompiledBy(): string
    {
        return $this->compiledBy;
    }

    public function setCompiledBy(string $compiledBy): self
    {
        $this->compiledBy = $compiledBy;

        return $this;
    }

    public function getGallery(): ?Gallery
    {
        return $this->gallery;
    }

    public function setGallery(?Gallery $gallery): self
    {
        $this->gallery = $gallery;

        return $this;
    }

    public function getPhotoBy(): ?string
    {
        return $this->photoBy;
    }

    public function setPhotoBy(?string $photoBy): self
    {
        $this->photoBy = $photoBy;

        return $this;
    }
}
