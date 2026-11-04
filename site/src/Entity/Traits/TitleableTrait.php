<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait TitleableTrait
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 254)]
    #[ORM\Column(name: 'title', type: Types::STRING, length: 255, nullable: false)]
    private string $title = '';

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }
}
