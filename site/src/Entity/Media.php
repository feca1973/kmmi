<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use App\Enum\MediaType;
use Doctrine\DBAL\Types\Types;
use App\Entity\Traits\ActivableTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TitleableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
class Media
{
    use IdTrait;
    use TitleableTrait;
    use ActivableTrait;

    // The column comment is a legacy (Doctrine 2 type hint) leftover; kept so the schema diff stays empty.
    #[ORM\Column(
        name: 'type',
        type: Types::ENUM,
        nullable: false,
        enumType: MediaType::class,
        options: ['comment' => '(DC2Type:MediaType)'],
    )]
    private MediaType $type;

    #[ORM\Column(name: 'path', type: Types::STRING, length: 255)]
    private ?string $path = null;

    #[ORM\Column(name: '`lead`', type: Types::TEXT, nullable: true)]
    private ?string $lead = null;

    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(name: 'slug', type: Types::STRING, length: 255, nullable: false)]
    private ?string $slug = null;

    /**
     * @var Collection<int, Attachment>
     */
    #[ORM\OneToMany(
        targetEntity: Attachment::class,
        mappedBy: 'media',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    private Collection $attachments;

    public function __construct()
    {
        $this->attachments = new ArrayCollection();
    }


    public function getType(): MediaType
    {
        return $this->type;
    }

    public function setType(MediaType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getLead(): ?string
    {
        return $this->lead;
    }

    public function setLead(?string $lead): self
    {
        $this->lead = $lead;

        return $this;
    }

    public function setSlug(?string $slug): ?Media
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
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
            $attachment->setMedia($this);
        }

        return $this;
    }

    public function removeAttachment(Attachment $attachment): self
    {
        if ($this->attachments->contains($attachment)) {
            $this->attachments->removeElement($attachment);
            // set the owning side to null (unless already changed)
            if ($attachment->getMedia() === $this) {
                $attachment->setMedia(null);
            }
        }

        return $this;
    }
}
