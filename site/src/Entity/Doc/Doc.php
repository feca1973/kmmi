<?php

namespace App\Entity\Doc;

use App\Repository\Doc\DocRepository;
use Doctrine\DBAL\Types\Types;
use App\Entity\Traits\TranslatableTrait;
use App\Entity\Attachment;
use App\Entity\Traits\ActivableTrait;
use App\Entity\Traits\BlameableTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Docs
 *
 * @method string|null getTitle()
 * @method string|null getSlug()
 * @method string|null getLead()
 * @method string|null getText()
 */
#[ORM\Table(name: 'docs')]
#[ORM\Entity(repositoryClass: DocRepository::class)]
class Doc
{
    use TranslatableTrait;
    use IdTrait;
    use BlameableTrait;
    use TimestampableTrait;
    use ActivableTrait;

    /**
     * The "About us" page and the last one of the pages beside it (the departments, the contacts): the ids of the live
     * data. The pages up to LAST_ABOUT_ID list the departments in their sidebar.
     */
    public const ABOUT_ID = 1;
    public const LAST_ABOUT_ID = 5;

    public function __toString(): string
    {
        return (string) $this->getDefaultTitle();
    }

    public function getDefaultTitle(): ?string
    {
        return $this->getTitle();
    }

    #[ORM\Column(name: 'image', type: Types::STRING, length: 255, nullable: true)]
    private ?string $image = null;

    /**
     * @var Collection<int, Attachment>
     */
    #[ORM\OneToMany(
        targetEntity: Attachment::class,
        mappedBy: 'doc',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    private Collection $attachments;

    /**
     * @var Collection<string, DocTranslation>
     */
    #[Assert\Valid]
    #[ORM\OneToMany(
        targetEntity: DocTranslation::class,
        mappedBy: 'translatable',
        indexBy: 'locale',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    protected Collection $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->attachments = new ArrayCollection();
    }

    public function setImage(?string $image): Doc
    {
        $this->image = $image;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
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
            $attachment->setDoc($this);
        }

        return $this;
    }

    public function removeAttachment(Attachment $attachment): self
    {
        if ($this->attachments->contains($attachment)) {
            $this->attachments->removeElement($attachment);
            // set the owning side to null (unless already changed)
            if ($attachment->getDoc() === $this) {
                $attachment->setDoc(null);
            }
        }

        return $this;
    }
}
