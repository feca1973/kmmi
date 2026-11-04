<?php

namespace App\Entity\Unique;

use App\Repository\Unique\UniqueItemRepository;
use Doctrine\DBAL\Types\Types;
use App\Enum\UniqueType;
use App\Entity\Traits\IdTrait;
use App\Entity\Traits\TitleableTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * UniqueItem
 */
#[ORM\Table(name: 'unique_items')]
#[ORM\Index(name: 'IDX_D9931E12FE54D947', columns: ['group_id'])]
#[ORM\Entity(repositoryClass: UniqueItemRepository::class)]
class UniqueItem
{
    use IdTrait;
    use TitleableTrait;

    #[Gedmo\Slug(fields: ['title'], updatable: false)]
    #[ORM\Column(name: 'slug', type: Types::STRING, length: 255, nullable: false)]
    private ?string $slug = null;

    #[ORM\Column(name: 'content', type: Types::STRING, nullable: true)]
    private ?string $content = null;

    // The column comment is a legacy (Doctrine 2 type hint) leftover; kept so the schema diff stays empty.
    #[ORM\Column(
        name: 'type',
        type: Types::ENUM,
        nullable: true,
        enumType: UniqueType::class,
        options: ['comment' => '(DC2Type:UniqueType)'],
    )]
    private ?UniqueType $type = null;

    #[ORM\JoinColumn(name: 'group_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: UniqueGroup::class, inversedBy: 'uniqueItems')]
    private ?UniqueGroup $uniqueGroup = null;

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): UniqueItem
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContent(): ?string
    {
        return ($this->type == UniqueType::Elfinder) ? urldecode($this->content) : $this->content;
    }

    public function setContent(?string $content): UniqueItem
    {
        $this->content = $content;

        return $this;
    }

    public function getType(): ?UniqueType
    {
        return $this->type;
    }

    public function setType(?UniqueType $type): UniqueItem
    {
        $this->type = $type;

        return $this;
    }

    public function getUniqueGroup(): ?UniqueGroup
    {
        return $this->uniqueGroup;
    }

    public function setUniqueGroup(?UniqueGroup $uniqueGroup): UniqueItem
    {
        $this->uniqueGroup = $uniqueGroup;

        return $this;
    }
}
