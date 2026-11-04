<?php

namespace App\Entity\Organization;

use App\Repository\Organization\OrganizationRepository;
use Doctrine\DBAL\Types\Types;
use App\Entity\Traits\TranslatableTrait;
use App\Entity\Attachment;
use App\Entity\Gallery\Gallery;
use App\Entity\Event\Events;
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
 * @method string|null getPlaceAddress()
 * @method string|null getOfficialPersonName()
 * @method string|null getContactPersonName()
 * @method string|null getFieldOfActivityId()
 * @method string|null getGoal()
 * @method string|null getServices()
 * @method string|null getIntroduction()
 * @method string|null getOfficialPersonShortCv()
 *
 * Maps the legacy `organizations` table, which has this many columns.
 *
 * @SuppressWarnings("PHPMD.TooManyFields")
 */
#[ORM\Table(name: 'organizations')]
#[ORM\Entity(repositoryClass: OrganizationRepository::class)]
class Organization
{
    use IdTrait;
    use BlameableTrait;
    use TimestampableTrait;
    use ActivableTrait;
    use TranslatableTrait;

    #[ORM\Column(name: 'country_id', type: Types::STRING, length: 2, nullable: true)]
    private ?string $countryId = null;

    #[ORM\ManyToOne(targetEntity: Cities::class, inversedBy: 'organizations')]
    private ?Cities $city = null;

    #[ORM\ManyToOne(targetEntity: OrganizationType::class, inversedBy: 'organizations')]
    private ?OrganizationType $type = null;

    #[ORM\Column(name: 'official_person_telephone_home', type: Types::STRING, length: 255, nullable: true)]
    private ?string $officialPersonTelephoneHome = null;

    #[ORM\Column(name: 'official_person_telephone_office', type: Types::STRING, length: 255, nullable: true)]
    private ?string $officialPersonTelephoneOffice = null;

    #[ORM\Column(name: 'official_person_telephone_mobile', type: Types::STRING, length: 255, nullable: true)]
    private ?string $officialPersonTelephoneMobile = null;

    #[ORM\Column(name: 'official_person_fax', type: Types::STRING, length: 255, nullable: true)]
    private ?string $officialPersonFax = null;

    #[ORM\Column(name: 'official_person_email', type: Types::STRING, length: 255, nullable: true)]
    private ?string $officialPersonEmail = null;

    #[ORM\Column(name: 'official_email', type: Types::STRING, length: 255, nullable: true)]
    private ?string $officialEmail = null;

    #[ORM\Column(name: 'contact_telephone_office', type: Types::STRING, length: 255, nullable: true)]
    private ?string $contactTelephoneOffice = null;

    #[ORM\Column(name: 'contact_telephone_mobile', type: Types::STRING, length: 255, nullable: true)]
    private ?string $contactTelephoneMobile = null;

    #[ORM\Column(name: 'contact_fax', type: Types::STRING, length: 255, nullable: true)]
    private ?string $contactFax = null;

    #[ORM\Column(name: 'contact_email', type: Types::STRING, length: 255, nullable: true)]
    private ?string $contactEmail = null;

    #[ORM\Column(name: 'contact_url', type: Types::STRING, length: 255, nullable: true)]
    private ?string $contactUrl = null;

    #[ORM\Column(name: 'institution_year', type: Types::STRING, length: 255, nullable: true)]
    private ?string $institutionYear = null;

    /**
     * @var Collection<string, OrganizationTranslation>
     */
    #[Assert\Valid]
    #[ORM\OneToMany(
        targetEntity: OrganizationTranslation::class,
        mappedBy: 'translatable',
        indexBy: 'locale',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    protected Collection $translations;

    /**
     * @var Collection<int, Events>
     */
    #[ORM\ManyToMany(targetEntity: Events::class, mappedBy: 'organizations')]
    private Collection $events;

    /**
     * @var Collection<int, Attachment>
     */
    #[ORM\OneToMany(
        targetEntity: Attachment::class,
        mappedBy: 'organization',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    private Collection $attachments;

    #[ORM\JoinColumn(name: 'gallery_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Gallery::class, inversedBy: 'organizations')]
    private ?Gallery $gallery = null;

    public function __toString(): string
    {
        return (string) $this->getDefaultTitle();
    }

    public function getDefaultTitle(): ?string
    {
        return $this->getTitle();
    }

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->attachments = new ArrayCollection();
    }

    public function getCountryId(): ?string
    {
        return $this->countryId;
    }

    public function setCountryId(?string $countryId): self
    {
        $this->countryId = $countryId;

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

    public function getType(): ?OrganizationType
    {
        return $this->type;
    }

    public function setType(?OrganizationType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getOfficialPersonTelephoneHome(): ?string
    {
        return $this->officialPersonTelephoneHome;
    }

    public function setOfficialPersonTelephoneHome(?string $officialPersonTelephoneHome): self
    {
        $this->officialPersonTelephoneHome = $officialPersonTelephoneHome;

        return $this;
    }

    public function getOfficialPersonTelephoneOffice(): ?string
    {
        return $this->officialPersonTelephoneOffice;
    }

    public function setOfficialPersonTelephoneOffice(?string $officialPersonTelephoneOffice): self
    {
        $this->officialPersonTelephoneOffice = $officialPersonTelephoneOffice;

        return $this;
    }

    public function getOfficialPersonTelephoneMobile(): ?string
    {
        return $this->officialPersonTelephoneMobile;
    }

    public function setOfficialPersonTelephoneMobile(?string $officialPersonTelephoneMobile): self
    {
        $this->officialPersonTelephoneMobile = $officialPersonTelephoneMobile;

        return $this;
    }

    public function getOfficialPersonFax(): ?string
    {
        return $this->officialPersonFax;
    }

    public function setOfficialPersonFax(?string $officialPersonFax): self
    {
        $this->officialPersonFax = $officialPersonFax;

        return $this;
    }

    public function getOfficialPersonEmail(): ?string
    {
        return $this->officialPersonEmail;
    }

    public function setOfficialPersonEmail(?string $officialPersonEmail): self
    {
        $this->officialPersonEmail = $officialPersonEmail;

        return $this;
    }

    public function getOfficialEmail(): ?string
    {
        return $this->officialEmail;
    }

    public function setOfficialEmail(?string $officialEmail): self
    {
        $this->officialEmail = $officialEmail;

        return $this;
    }

    public function getContactTelephoneOffice(): ?string
    {
        return $this->contactTelephoneOffice;
    }

    public function setContactTelephoneOffice(?string $contactTelephoneOffice): self
    {
        $this->contactTelephoneOffice = $contactTelephoneOffice;

        return $this;
    }

    public function getContactTelephoneMobile(): ?string
    {
        return $this->contactTelephoneMobile;
    }

    public function setContactTelephoneMobile(?string $contactTelephoneMobile): self
    {
        $this->contactTelephoneMobile = $contactTelephoneMobile;

        return $this;
    }

    public function getContactFax(): ?string
    {
        return $this->contactFax;
    }

    public function setContactFax(?string $contactFax): self
    {
        $this->contactFax = $contactFax;

        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(?string $contactEmail): self
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    public function getContactUrl(): ?string
    {
        return $this->contactUrl;
    }

    public function setContactUrl(?string $contactUrl): self
    {
        $this->contactUrl = $contactUrl;

        return $this;
    }

    public function getInstitutionYear(): ?string
    {
        return $this->institutionYear;
    }

    public function setInstitutionYear(?string $institutionYear): self
    {
        $this->institutionYear = $institutionYear;

        return $this;
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
            $event->addOrganization($this);
        }

        return $this;
    }

    public function removeEvent(Events $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            $event->removeOrganization($this);
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
            $attachment->setOrganization($this);
        }

        return $this;
    }

    public function removeAttachment(Attachment $attachment): self
    {
        if ($this->attachments->contains($attachment)) {
            $this->attachments->removeElement($attachment);
            // set the owning side to null (unless already changed)
            if ($attachment->getOrganization() === $this) {
                $attachment->setOrganization(null);
            }
        }

        return $this;
    }

    public function setGallery(?Gallery $gallery): self
    {
        $this->gallery = $gallery;

        return $this;
    }

    public function getGallery(): ?Gallery
    {
        return $this->gallery;
    }
}
