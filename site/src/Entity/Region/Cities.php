<?php

namespace App\Entity\Region;

use App\Repository\Region\CitiesRepository;
use Doctrine\DBAL\Types\Types;
use App\Entity\Traits\TranslatableTrait;
use App\Entity\Article\Article;
use App\Entity\Event\Events;
use App\Entity\Organization\Organization;
use App\Entity\Traits\ActivableTrait;
use App\Entity\Traits\BlameableTrait;
use App\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\IdTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @method string|null getTitle()
 * @method string|null getSlug()
 * @method string|null getLead()
 * @method string|null getContent()
 */
#[ORM\Table(name: 'cities')]
#[ORM\Entity(repositoryClass: CitiesRepository::class)]
class Cities
{
    use IdTrait;
    use BlameableTrait;
    use TimestampableTrait;
    use ActivableTrait;
    use TranslatableTrait;

    #[ORM\Column(name: 'image', type: Types::STRING, length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\JoinColumn(name: 'regions_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Regions::class, inversedBy: 'cities')]
    private ?Regions $regions = null;

    /**
     * @var Collection<int, Events>
     */
    #[ORM\OneToMany(targetEntity: Events::class, mappedBy: 'city')]
    private Collection $events;

    /**
     * @var Collection<int, Organization>
     */
    #[ORM\OneToMany(targetEntity: Organization::class, mappedBy: 'city')]
    private Collection $organizations;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'city')]
    private Collection $articles;

    /**
     * @var Collection<string, CitiesTranslation>
     */
    #[Assert\Valid]
    #[ORM\OneToMany(
        targetEntity: CitiesTranslation::class,
        mappedBy: 'translatable',
        indexBy: 'locale',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    protected Collection $translations;

    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->organizations = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getDefaultTitle();
    }

    public function getDefaultTitle(): ?string
    {
        return $this->getTitle();
    }

    public function setImage(?string $image): Cities
    {
        $this->image = $image;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setRegions(?Regions $regions): Cities
    {
        $this->regions = $regions;

        return $this;
    }

    public function getRegions(): ?Regions
    {
        return $this->regions;
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
            $event->setCity($this);
        }

        return $this;
    }

    public function removeEvent(Events $event): self
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            // set the owning side to null (unless already changed)
            if ($event->getCity() === $this) {
                $event->setCity(null);
            }
        }

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
            $organization->setCity($this);
        }

        return $this;
    }

    public function removeOrganization(Organization $organization): self
    {
        if ($this->organizations->contains($organization)) {
            $this->organizations->removeElement($organization);
            // set the owning side to null (unless already changed)
            if ($organization->getCity() === $this) {
                $organization->setCity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Article $article): self
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->setCity($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->contains($article)) {
            $this->articles->removeElement($article);
            // set the owning side to null (unless already changed)
            if ($article->getCity() === $this) {
                $article->setCity(null);
            }
        }

        return $this;
    }
}
