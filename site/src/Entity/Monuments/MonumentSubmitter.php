<?php

namespace App\Entity\Monuments;

use Doctrine\DBAL\Types\Types;
use App\Entity\Monuments\Monument;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\NameableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * MonumentSubmitter
 */
#[ORM\Table(name: 'monument_submitter')]
#[ORM\Entity]
class MonumentSubmitter
{
    use IdTrait;
    use NameableTrait;

    #[Gedmo\Slug(fields: ['name'])]
    #[ORM\Column(name: 'slug', type: Types::STRING, length: 255, nullable: false)]
    private ?string $slug = null;

    /**
     * @var Collection<int, Monument>
     */
    #[ORM\OneToMany(targetEntity: Monument::class, mappedBy: 'submitter')]
    private Collection $monuments;

    public function __toString(): string
    {
        return $this->name;
    }

    public function __construct()
    {
        $this->monuments = new ArrayCollection();
    }

    public function setSlug(?string $slug): ?MonumentSubmitter
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
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
            $monument->setSubmitter($this);
        }

        return $this;
    }

    public function removeMonument(Monument $monument): self
    {
        if ($this->monuments->contains($monument)) {
            $this->monuments->removeElement($monument);
            // set the owning side to null (unless already changed)
            if ($monument->getSubmitter() === $this) {
                $monument->setSubmitter(null);
            }
        }

        return $this;
    }
}
