<?php

namespace App\Entity\Article;

use Doctrine\DBAL\Types\Types;
use App\Entity\Traits\NameableTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Traits\TranslationTrait;

/**
 * Doctrine entity: the `translatable` association is read through TranslationTrait.
 *
 * @SuppressWarnings("PHPMD.UnusedPrivateField")
 */
#[ORM\Table(name: 'article_type_translations')]
#[ORM\UniqueConstraint(columns: ['translatable_id', 'locale'])]
#[ORM\Entity]
class ArticleTypeTranslation
{
    use TranslationTrait;
    use NameableTrait;

    #[ORM\ManyToOne(targetEntity: ArticleType::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'translatable_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?object $translatable = null;

    #[Gedmo\Slug(fields: ['name'], updatable: false)]
    #[ORM\Column(name: 'slug', type: Types::STRING, length: 255, nullable: false)]
    private ?string $slug = null;

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function setName(?string $name): ArticleTypeTranslation
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setSlug(?string $slug): ArticleTypeTranslation
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }
}
