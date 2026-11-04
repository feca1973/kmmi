<?php

namespace App\Entity\Article;

use App\Repository\Article\ActorTypeRepository;
use Doctrine\DBAL\Types\Types;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\NameableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ActorType
 */
#[ORM\Table(name: 'actors_type')]
#[ORM\Entity(repositoryClass: ActorTypeRepository::class)]
class ActorType
{
    use IdTrait;
    use NameableTrait;

    #[Gedmo\Slug(fields: ['name'])]
    #[ORM\Column(name: 'slug', type: Types::STRING, length: 255, nullable: false)]
    private ?string $slug = null;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'actorType')]
    private Collection $articles;

    public function __toString(): string
    {
        return $this->name;
    }

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    public function setSlug(?string $slug): ?ActorType
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
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
            $article->setActorType($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->contains($article)) {
            $this->articles->removeElement($article);
            // set the owning side to null (unless already changed)
            if ($article->getActorType() === $this) {
                $article->setActorType(null);
            }
        }

        return $this;
    }
}
