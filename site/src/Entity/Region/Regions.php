<?php

namespace App\Entity\Region;

use Doctrine\DBAL\Types\Types;
use App\Entity\Traits\TitleableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Traits\IdTrait;

#[ORM\Entity]
class Regions
{
    use IdTrait;
    use TitleableTrait;

    #[Gedmo\Slug(fields: ['title'])]
    #[ORM\Column(name: 'slug', type: Types::STRING, length: 255, nullable: false)]
    private ?string $slug = null;

    /**
     * @var Collection<int, Cities>
     */
    #[ORM\OneToMany(targetEntity: Cities::class, mappedBy: 'regions', cascade: ['persist'])]
    protected Collection $cities;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cities = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getDefaultTitle();
    }

    public function getDefaultTitle(): ?string
    {
        return $this->getTitle();
    }

    public function setSlug(?string $slug): ?Regions
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function addCity(Cities $city): Regions
    {
        $this->cities->add($city);

        return $this;
    }

    public function removeCity(Cities $city): Regions
    {
        $city->setRegions(null);
        $this->cities->removeElement($city);

        return $this;
    }

    /**
     * @return Collection<int, Cities>
     */
    public function getCities(): Collection
    {
        return $this->cities;
    }
}
