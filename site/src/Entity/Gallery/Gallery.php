<?php

namespace App\Entity\Gallery;

use App\Repository\Gallery\GalleryRepository;
use Doctrine\DBAL\Types\Types;
use App\Entity\Article\Article;
use App\Entity\Monuments\Monument;
use App\Entity\Organization\Organization;
use App\Entity\Traits\ActivableTrait;
use App\Entity\Traits\BlameableTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\NameableTrait;
use App\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy as OrderBy;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Gallery
 */
#[ORM\Table(name: 'gallery')]
#[ORM\Entity(repositoryClass: GalleryRepository::class)]
class Gallery
{
    use IdTrait;
    use NameableTrait;
    use BlameableTrait;
    use TimestampableTrait;
    use ActivableTrait;

    #[Gedmo\Slug(fields: ['name'])]
    #[ORM\Column(name: 'slug', type: Types::STRING, length: 255, nullable: false)]
    private ?string $slug = null;

    #[ORM\Column(name: 'cover_image', type: Types::STRING, length: 255, nullable: true)]
    private ?string $coverImage = null;

    /**
     * @var Collection<int, GalleryImage>
     */
    #[ORM\OneToMany(
        targetEntity: GalleryImage::class,
        mappedBy: 'gallery',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    #[OrderBy(['position' => 'ASC'])]
    private Collection $galleryImages;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\OneToMany(targetEntity: Article::class, mappedBy: 'gallery')]
    private Collection $articles;

    /**
     * @var Collection<int, Organization>
     */
    #[ORM\OneToMany(targetEntity: Organization::class, mappedBy: 'gallery')]
    private Collection $organizations;

    /**
     * @var Collection<int, Monument>
     */
    #[ORM\OneToMany(targetEntity: Monument::class, mappedBy: 'gallery')]
    private Collection $monuments;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->galleryImages = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->organizations = new ArrayCollection();
        $this->monuments = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function setSlug(?string $slug): Gallery
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setCoverImage(?string $coverImage): Gallery
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function addGalleryImage(GalleryImage $galleryImage): Gallery
    {
        $this->galleryImages->add($galleryImage);
        $galleryImage->setGallery($this);

        return $this;
    }

    public function removeGalleryImage(GalleryImage $galleryImage): Gallery
    {
        $galleryImage->setGallery();
        $this->galleryImages->removeElement($galleryImage);

        return $this;
    }

    /**
     * @return Collection<int, GalleryImage>
     */
    public function getGalleryImages(): Collection
    {

        return $this->galleryImages;
    }


    public function addArticle(Article $article): Gallery
    {
        $this->articles->add($article);

        return $this;
    }


    public function removeArticle(Article $article): Gallery
    {
        $this->articles->removeElement($article);

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
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
            $organization->setGallery($this);
        }

        return $this;
    }

    public function removeOrganization(Organization $organization): self
    {
        if ($this->organizations->contains($organization)) {
            $this->organizations->removeElement($organization);
            // set the owning side to null (unless already changed)
            if ($organization->getGallery() === $this) {
                $organization->setGallery(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Monument>
     */
    public function getMonuments(): Collection
    {
        return $this->monuments;
    }

    public function addMonument(Monument $monument): self
    {
        if (!$this->monuments->contains($monument)) {
            $this->monuments->add($monument);
            $monument->setGallery($this);
        }

        return $this;
    }

    public function removeMonument(Monument $monument): self
    {
        if ($this->monuments->contains($monument)) {
            $this->monuments->removeElement($monument);
            // set the owning side to null (unless already changed)
            if ($monument->getGallery() === $this) {
                $monument->setGallery(null);
            }
        }

        return $this;
    }
}
