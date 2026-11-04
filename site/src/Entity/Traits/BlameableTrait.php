<?php

namespace App\Entity\Traits;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait BlameableTrait
{
    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[Gedmo\Blameable(on: 'create')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $author = null;

    #[ORM\JoinColumn(name: 'editor_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[Gedmo\Blameable(on: 'update')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $editor = null;

    public function setAuthor(?User $author = null): static
    {
        $this->author = $author;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setEditor(?User $editor = null): static
    {
        $this->editor = $editor;

        return $this;
    }

    public function getEditor(): ?User
    {
        return $this->editor;
    }
}
