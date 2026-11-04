<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait ActivableTrait
{
    #[ORM\Column(name: 'active', type: Types::BOOLEAN, nullable: true)]
    private ?bool $active = null;

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }
}
