<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Common columns of the `<table>_translation` tables. The using class declares the `translatable`
 * association itself (the target entity differs per class) and the (translatable_id, locale) unique constraint.
 */
trait TranslationTrait
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'id', type: Types::INTEGER, nullable: false)]
    private ?int $id = null;

    #[ORM\Column(name: 'locale', type: Types::STRING, length: 10, nullable: false)]
    private string $locale = 'hu';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTranslatable(): ?object
    {
        return $this->translatable;
    }

    public function setTranslatable(?object $translatable): static
    {
        $this->translatable = $translatable;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }
}
