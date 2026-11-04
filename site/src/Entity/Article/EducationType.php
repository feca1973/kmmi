<?php

namespace App\Entity\Article;

use App\Repository\Article\EducationTypeRepository;
use Doctrine\DBAL\Types\Types;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\NameableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * EducationType
 */
#[ORM\Table(name: 'education_type')]
#[ORM\Entity(repositoryClass: EducationTypeRepository::class)]
class EducationType
{
    use IdTrait;
    use NameableTrait;

    #[Gedmo\Slug(fields: ['name'])]
    #[ORM\Column(name: 'slug', type: Types::STRING, length: 255, nullable: false)]
    private ?string $slug = null;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'educationType')]
    private Collection $articles;

    public function __toString(): string
    {
        return $this->name;
    }

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    public function setSlug(?string $slug): ?EducationType
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
            $article->setEducationType($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->contains($article)) {
            $this->articles->removeElement($article);
            // set the owning side to null (unless already changed)
            if ($article->getEducationType() === $this) {
                $article->setEducationType(null);
            }
        }

        return $this;
    }
}
