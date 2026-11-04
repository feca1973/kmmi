<?php

namespace App\Twig\Components;

use App\Entity\Doc\Doc;
use App\Repository\Doc\DocRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * The departments and the contacts, listed beside the "About us" pages.
 */
#[AsTwigComponent]
class RelatedDocs
{
    public int $activeId = 0;

    public function __construct(private readonly DocRepository $docs)
    {
    }

    /**
     * @return list<Doc>
     */
    public function getDocs(): array
    {
        return $this->docs->findDepartments();
    }
}
