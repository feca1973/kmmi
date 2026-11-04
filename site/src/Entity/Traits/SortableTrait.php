<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait SortableTrait
{
    #[ORM\Column(name: 'sorting', type: Types::INTEGER, nullable: true)]
    private ?int $sorting = null;

    public function setSorting(?int $sorting): static
    {
        $this->sorting = $sorting;

        return $this;
    }

    /**
     * Get sorting
     *
     * @return int|null
     */
    public function getSorting(): ?int
    {
        return $this->sorting;
    }
}
