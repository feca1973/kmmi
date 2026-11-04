<?php

namespace App\Entity\Menu;

use App\Repository\Menu\MenuItemRepository;
use Doctrine\DBAL\Types\Types;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TitleableTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * MenuItem
 */
#[ORM\Table(name: 'menu_items')]
#[ORM\Entity(repositoryClass: MenuItemRepository::class)]
class MenuItem
{
    use IdTrait;
    use TitleableTrait;

    public const CUSTOM_URL = 'custom_url';

    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: MenuItem::class, inversedBy: 'children')]
    #[ORM\OrderBy(['title' => 'ASC'])]
    private ?MenuItem $parent = null;

    /**
     * @var Collection<int, MenuItem>
     */
    #[ORM\OneToMany(
        targetEntity: MenuItem::class,
        mappedBy: 'parent',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $children;

    #[ORM\JoinColumn(name: 'menu_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Menu::class, inversedBy: 'menuItems')]
    private ?Menu $menu = null;

    #[ORM\Column(name: 'connected_type', type: Types::STRING, length: 255, nullable: true)]
    private ?string $connectedType = null;

    #[ORM\Column(name: 'connected_value', type: Types::STRING, nullable: true)]
    private ?string $connectedValue = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }

    public function addChild(MenuItem $child): MenuItem
    {
        $this->children->add($child);
        $child->setParent($this);

        return $this;
    }

    public function removeChild(MenuItem $child): MenuItem
    {
        $this->children->removeElement($child);

        return $this;
    }

    /**
     * @return Collection<int, MenuItem>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * The target of the item; only items linked to a custom URL have one.
     */
    public function getUrl(): ?string
    {
        if ($this->connectedType === self::CUSTOM_URL && (string) $this->connectedValue !== '') {
            return $this->connectedValue;
        }

        return null;
    }

    /**
     * The menu editor edits the target as a URL: the item is linked to that custom URL (an empty one clears the link).
     */
    public function setUrl(?string $url): MenuItem
    {
        $url = trim((string) $url);
        $this->connectedType = $url === '' ? null : self::CUSTOM_URL;
        $this->connectedValue = $url === '' ? null : $url;

        return $this;
    }

    public function setParent(?MenuItem $parent): MenuItem
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent(): ?MenuItem
    {
        return $this->parent;
    }

    public function setMenu(?Menu $menu): MenuItem
    {
        $this->menu = $menu;

        return $this;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function isIndexable(): bool
    {
        $item = $this;
        while ($item->getParent() != null) {
            $item = $item->getParent();
        }

        return ($item->menu != null);
    }

    public function getConnectedType(): ?string
    {
        return $this->connectedType;
    }

    public function setConnectedType(?string $connectedType): MenuItem
    {
        $this->connectedType = $connectedType;

        return $this;
    }

    public function getConnectedValue(): ?string
    {
        return $this->connectedValue;
    }

    public function setConnectedValue(?string $connectedValue): MenuItem
    {
        $this->connectedValue = $connectedValue;

        return $this;
    }
}
