<?php

namespace App\Twig\Components;

use App\Entity\Monuments\Monument;
use App\Repository\Monuments\MonumentRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * The last updated monuments of the homepage and the sidebars.
 */
#[AsTwigComponent]
class SidebarMonuments
{
    public int $size = 3;

    public function __construct(private readonly MonumentRepository $monuments)
    {
    }

    /**
     * @return list<Monument>
     */
    public function getMonuments(): array
    {
        return $this->monuments->findLastUpdated($this->size);
    }
}
