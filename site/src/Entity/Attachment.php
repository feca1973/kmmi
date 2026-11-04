<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use App\Entity\Doc\Doc;
use App\Entity\Article\Article;
use App\Entity\Event\Events;
use App\Entity\Organization\Organization;
use App\Entity\Traits\BlameableTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Attachments
 */
#[ORM\Table(name: 'attachments')]
#[ORM\Entity]
class Attachment
{
    use IdTrait;
    use TimestampableTrait;
    use BlameableTrait;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(name: 'path', type: Types::STRING, length: 255, nullable: false)]
    private string $path = '';

    #[ORM\JoinColumn(name: 'doc_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Doc::class, inversedBy: 'attachments')]
    private ?Doc $doc = null;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'attachments')]
    private ?Article $article = null;

    #[ORM\ManyToOne(targetEntity: Events::class, inversedBy: 'attachments')]
    private ?Events $event = null;

    #[ORM\ManyToOne(targetEntity: Organization::class, inversedBy: 'attachments')]
    private ?Organization $organization = null;

    #[ORM\ManyToOne(targetEntity: Media::class, inversedBy: 'attachments')]
    private ?Media $media = null;

    public function __toString(): string
    {
        return $this->name;
    }

    public function getContent(): ?string
    {
        $content = file_get_contents(dirname(__DIR__, 2) . '/public' . $this->path);

        return $content === false ? null : $content;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param  string $name
     * @return $this
     */
    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function setPath(?string $path): Attachment
    {
        $this->path = $path;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setDoc(?Doc $doc): Attachment
    {
        $this->doc = $doc;

        return $this;
    }

    public function getDoc(): ?Doc
    {
        return $this->doc;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function getEvent(): ?Events
    {
        return $this->event;
    }

    public function setEvent(?Events $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): self
    {
        $this->media = $media;

        return $this;
    }
}
