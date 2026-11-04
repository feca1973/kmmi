<?php

namespace App\Twig\Components;

use App\Entity\Gallery\Gallery;
use App\Repository\Gallery\GalleryRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * The last updated galleries of the homepage and the sidebars.
 */
#[AsTwigComponent]
class SidebarGalleries
{
    public int $size = 3;

    public function __construct(private readonly GalleryRepository $galleries)
    {
    }

    /**
     * @return list<Gallery>
     */
    public function getGalleries(): array
    {
        return $this->galleries->findLastUpdated($this->size);
    }
}
