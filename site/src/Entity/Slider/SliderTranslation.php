<?php

namespace App\Entity\Slider;

use Doctrine\DBAL\Types\Types;
use App\Entity\Traits\TranslationTrait;
use App\Entity\Traits\TitleableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Doctrine entity: the `translatable` association is read through TranslationTrait.
 *
 * @SuppressWarnings("PHPMD.UnusedPrivateField")
 */
#[ORM\Table(name: 'slider_translation')]
#[ORM\UniqueConstraint(columns: ['translatable_id', 'locale'])]
#[ORM\Entity]
class SliderTranslation
{
    use TranslationTrait;
    use TitleableTrait;

    #[ORM\ManyToOne(targetEntity: Slider::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'translatable_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?object $translatable = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(name: 'title_text', type: Types::STRING, length: 255, nullable: true)]
    private ?string $titleText = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(name: 'subtitle', type: Types::STRING, length: 255, nullable: true)]
    private ?string $subtitle = null;

    #[ORM\Column(name: 'url', type: Types::STRING, length: 255, nullable: true)]
    private ?string $url = null;


    public function __toString(): string
    {
        return $this->title;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get titleText
     *
     * @return string
     */
    public function getTitleText(): ?string
    {
        return $this->titleText;
    }

    /**
     * Set titleText
     *
     * @param  string $titleText
     * @return $this
     */
    public function setTitleText(?string $titleText): static
    {
        $this->titleText = $titleText;

        return $this;
    }

    /**
     * Get subtitle
     *
     * @return string
     */
    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    /**
     * Set subtitle
     *
     * @param  string $subtitle
     * @return $this
     */
    public function setSubtitle(?string $subtitle): static
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * Set url
     *
     * @param  string $url
     */
    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }
}
