<?php

namespace App\Entity\Menu;

use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TitleableTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Menu
 */
#[ORM\Table(name: 'menus')]
#[ORM\Entity]
class Menu
{
    use IdTrait;
    use TitleableTrait;


    /**
     * @var Collection<int, MenuItem>
     */
    #[ORM\OneToMany(
        targetEntity: MenuItem::class,
        mappedBy: 'menu',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    #[ORM\OrderBy(['id' => 'ASC'])]
    protected Collection $menuItems;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->menuItems = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }

    public function addMenuItem(MenuItem $menuItem): Menu
    {
        $this->menuItems->add($menuItem);
        $menuItem->setMenu($this);

        return $this;
    }

    public function removeMenuItem(MenuItem $menuItem): Menu
    {
        $this->menuItems->removeElement($menuItem);

        return $this;
    }

    /**
     * @return Collection<int, MenuItem>
     */
    public function getMenuItems(): Collection
    {

        return $this->menuItems;
    }
}
