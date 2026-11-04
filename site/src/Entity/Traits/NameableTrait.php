<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait NameableTrait
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 254)]
    #[ORM\Column(name: 'name', type: Types::STRING, length: 255, nullable: false)]
    private string $name = '';

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param  string $name
     * @return $this
     */
    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
