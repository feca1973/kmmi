<?php

namespace App\Entity\Article;

use App\Repository\Article\BookCategoryRepository;
use Doctrine\DBAL\Types\Types;
use App\Entity\Traits\ActivableTrait;
use App\Entity\Traits\IdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * BookCategory
 */
#[ORM\Table(name: 'book_categories', options: ['charset' => 'utf8mb3', 'collation' => 'utf8mb3_general_ci'])]
#[ORM\Entity(repositoryClass: BookCategoryRepository::class)]
class BookCategory
{
    use IdTrait;
    use ActivableTrait;

    // Not NameableTrait: this legacy column is utf8mb4 inside a utf8 table, which needs explicit options.
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 254)]
    #[ORM\Column(
        name: 'name',
        type: Types::STRING,
        length: 255,
        nullable: false,
        options: ['charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci'],
    )]
    private string $name = '';

    #[Gedmo\Slug(fields: ['name'])]
    #[ORM\Column(name: 'slug', type: Types::STRING, length: 255, nullable: false)]
    private ?string $slug = null;

    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: BookCategory::class, inversedBy: 'children')]
    #[ORM\OrderBy(['name' => 'ASC'])]
    private ?BookCategory $parent = null;

    /**
     * @var Collection<int, BookCategory>
     */
    #[ORM\OneToMany(
        targetEntity: BookCategory::class,
        mappedBy: 'parent',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    private Collection $children;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'bookCategory')]
    private Collection $articles;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->articles = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function setSlug(?string $slug): ?BookCategory
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function addChild(BookCategory $child): BookCategory
    {
        $this->children->add($child);
        $child->setParent($this);

        return $this;
    }

    public function removeChild(BookCategory $child): BookCategory
    {
        $this->children->removeElement($child);

        return $this;
    }

    /**
     * @return Collection<int, BookCategory>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function setParent(?BookCategory $parent): BookCategory
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent(): ?BookCategory
    {
        return $this->parent;
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
            $article->setBookCategory($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles->contains($article)) {
            $this->articles->removeElement($article);
            // set the owning side to null (unless already changed)
            if ($article->getBookCategory() === $this) {
                $article->setBookCategory(null);
            }
        }

        return $this;
    }
}
