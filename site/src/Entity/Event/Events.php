<?php

namespace App\Entity\Event;

use App\Repository\Event\EventsRepository;
use Doctrine\DBAL\Types\Types;
use DateTimeInterface;
use App\Entity\Traits\TranslatableTrait;
use App\Entity\Attachment;
use App\Entity\Organization\Organization;
use App\Entity\Region\Cities;
use App\Entity\Traits\ActivableTrait;
use App\Entity\Traits\BlameableTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @method string|null getTitle()
 * @method string|null getSlug()
 * @method string|null getLead()
 * @method string|null getContent()
 * @method string|null getPlaceName()
 * @method string|null getContactPerson()
 *
 * Maps the legacy `events` table, which has this many columns.
 *
 * @SuppressWarnings("PHPMD.TooManyFields")
 */
#[ORM\Table(name: 'events')]
#[ORM\Entity(repositoryClass: EventsRepository::class)]
class Events
{
    use IdTrait;
    use BlameableTrait;
    use TimestampableTrait;
    use ActivableTrait;
    use TranslatableTrait;

    #[ORM\Column(name: 'public_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTimeInterface $publicDate = null;

    #[ORM\Column(name: 'date_from', type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $dateFrom = null;

    #[ORM\Column(name: 'date_to', type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $dateTo = null;

    #[ORM\Column(name: 'country_id', type: Types::STRING, length: 2)]
    private ?string $countryId = null;

    #[ORM\Column(name: 'place_url', type: Types::STRING, length: 255, nullable: true)]
    private ?string $placeUrl = null;

    #[ORM\Column(name: 'telephone', type: Types::STRING, length: 255, nullable: true)]
    private ?string $telephone = null;

    #[ORM\Column(name: 'fax', type: Types::STRING, length: 255, nullable: true)]
    private ?string $fax = null;

    #[ORM\Column(name: 'email', type: Types::STRING, length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(name: 'url', type: Types::STRING, length: 255, nullable: true)]
    private ?string $url = null;

    #[ORM\Column(name: 'image', type: Types::STRING, length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: EventType::class, inversedBy: 'events')]
    private ?EventType $type = null;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Cities::class, inversedBy: 'events')]
    private ?Cities $city = null;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: EventPlaces::class, inversedBy: 'events')]
    private ?EventPlaces $place = null;

    /**
     * @var Collection<string, EventsTranslation>
     */
    #[Assert\Valid]
    #[ORM\OneToMany(
        targetEntity: EventsTranslation::class,
        mappedBy: 'translatable',
        indexBy: 'locale',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    protected Collection $translations;

    /**
     * @var Collection<int, Organization>
     */
    #[ORM\ManyToMany(targetEntity: Organization::class, inversedBy: 'events')]
    private Collection $organizations;

    /**
     * @var Collection<int, Attachment>
     */
    #[ORM\OneToMany(
        targetEntity: Attachment::class,
        mappedBy: 'event',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    private Collection $attachments;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->organizations = new ArrayCollection();
        $this->attachments = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getDefaultTitle();
    }

    public function getDefaultTitle(): ?string
    {
        return $this->getTitle();
    }

    public function getPublicDate(): ?DateTimeInterface
    {
        return $this->publicDate;
    }

    public function setPublicDate(?DateTimeInterface $publicDate): self
    {
        $this->publicDate = $publicDate;

        return $this;
    }

    public function getDateFrom(): ?DateTimeInterface
    {
        return $this->dateFrom;
    }

    public function setDateFrom(DateTimeInterface $dateFrom): self
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    public function getDateTo(): ?DateTimeInterface
    {
        return $this->dateTo;
    }

    public function setDateTo(DateTimeInterface $dateTo): self
    {
        $this->dateTo = $dateTo;

        return $this;
    }

    public function getCountryId(): ?string
    {
        return $this->countryId;
    }

    public function setCountryId(string $countryId): self
    {
        $this->countryId = $countryId;

        return $this;
    }

    public function getPlaceUrl(): ?string
    {
        return $this->placeUrl;
    }

    public function setPlaceUrl(?string $placeUrl): self
    {
        $this->placeUrl = $placeUrl;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function setFax(?string $fax): self
    {
        $this->fax = $fax;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getType(): ?EventType
    {
        return $this->type;
    }

    public function setType(?EventType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getCity(): ?Cities
    {
        return $this->city;
    }

    public function setCity(?Cities $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPlace(): ?EventPlaces
    {
        return $this->place;
    }

    public function setPlace(?EventPlaces $place): self
    {
        $this->place = $place;

        return $this;
    }

    /**
     * @return Collection<int, Organization>
     */
    public function getOrganizations(): Collection
    {
        return $this->organizations;
    }

    public function addOrganization(Organization $organization): self
    {
        if (!$this->organizations->contains($organization)) {
            $this->organizations->add($organization);
        }

        return $this;
    }

    public function removeOrganization(Organization $organization): self
    {
        if ($this->organizations->contains($organization)) {
            $this->organizations->removeElement($organization);
        }

        return $this;
    }

    /**
     * @return Collection<int, Attachment>
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(Attachment $attachment): self
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments->add($attachment);
            $attachment->setEvent($this);
        }

        return $this;
    }

    public function removeAttachment(Attachment $attachment): self
    {
        if ($this->attachments->contains($attachment)) {
            $this->attachments->removeElement($attachment);
            // set the owning side to null (unless already changed)
            if ($attachment->getEvent() === $this) {
                $attachment->setEvent(null);
            }
        }

        return $this;
    }
}
