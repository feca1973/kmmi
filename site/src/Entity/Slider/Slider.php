<?php

namespace App\Entity\Slider;

use Doctrine\Common\Collections\Collection;
use App\Entity\Traits\TranslatableTrait;
use App\Entity\Traits\ActivableTrait;
use App\Entity\Traits\BlameableTrait;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TimestampableTrait;
use App\Entity\Traits\SortableTrait;
use App\Entity\Traits\ImageableTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Articles
 *
 * @method string|null getTitle()
 */
#[ORM\Table(name: 'slider')]
#[ORM\Entity]
class Slider
{
    use IdTrait;
    use TranslatableTrait;
    use ActivableTrait;
    use BlameableTrait;
    use TimestampableTrait;
    use SortableTrait;
    use ImageableTrait;

    /**
     * @var Collection<string, SliderTranslation>
     */
    #[Assert\Valid]
    #[ORM\OneToMany(
        targetEntity: SliderTranslation::class,
        mappedBy: 'translatable',
        indexBy: 'locale',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    protected Collection $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getDefaultTitle();
    }

    public function getDefaultTitle(): ?string
    {
        return $this->getTitle();
    }
}
