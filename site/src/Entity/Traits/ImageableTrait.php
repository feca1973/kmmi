<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait ImageableTrait
{
    private bool $withUrldecode = true;

    #[ORM\Column(name: 'image', type: Types::STRING, length: 255, nullable: true)]
    private ?string $image = null;

    /**
     * Set image
     *
     * @param  string $image
     * @return $this
     */
    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage(): ?string
    {
        return ($this->withUrldecode == true) ? ($this->image != null) ? urldecode($this->image) : null : $this->image;
    }
}
