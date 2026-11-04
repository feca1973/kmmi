<?php

namespace App\Entity\Unique;

use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TitleableTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * UniqueGroup
 */
#[ORM\Table(name: 'unique_groups')]
#[ORM\Entity]
class UniqueGroup
{
    use IdTrait;
    use TitleableTrait;

    /**
     * @var Collection<int, UniqueItem>
     */
    #[ORM\OneToMany(
        targetEntity: UniqueItem::class,
        mappedBy: 'uniqueGroup',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    protected Collection $uniqueItems;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->uniqueItems = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }


    public function addUniqueItem(UniqueItem $uniqueItem): UniqueGroup
    {
        $this->uniqueItems->add($uniqueItem);
        $uniqueItem->setUniqueGroup($this);

        return $this;
    }

    public function removeUniqueItem(UniqueItem $uniqueItem): UniqueGroup
    {
        $this->uniqueItems->removeElement($uniqueItem);

        return $this;
    }


    /**
     * @return Collection<int, UniqueItem>
     */
    public function getUniqueItems(): Collection
    {

        return $this->uniqueItems;
    }
}
