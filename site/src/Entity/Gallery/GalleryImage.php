<?php

namespace App\Entity\Gallery;

use Doctrine\DBAL\Types\Types;
use App\Entity\Traits\IdTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * GalleryImages
 */
#[ORM\Table(name: 'gallery_images')]
#[ORM\Entity]
class GalleryImage
{
    use IdTrait;

    #[Assert\Length(min: 0, max: 254)]
    #[ORM\Column(name: 'name', type: Types::STRING, length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(name: 'path', type: Types::STRING, length: 255, nullable: false)]
    private string $path = '';

    #[ORM\JoinColumn(name: 'gallery_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Gallery::class, inversedBy: 'galleryImages')]
    private ?Gallery $gallery = null;

    #[ORM\Column(name: 'position', type: Types::INTEGER, nullable: true)]
    private ?int $position = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): GalleryImage
    {
        $this->name = $name;

        return $this;
    }

    public function setPath(?string $path): GalleryImage
    {
        $this->path = $path;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }


    public function setGallery(?Gallery $gallery = null): GalleryImage
    {
        $this->gallery = $gallery;

        return $this;
    }

    public function getGallery(): ?Gallery
    {
        return $this->gallery;
    }

    public function setPosition(?int $position): GalleryImage
    {
        $this->position = $position;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }
}
