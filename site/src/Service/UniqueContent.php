<?php

namespace App\Service;

use App\Repository\Unique\UniqueItemRepository;
use Twig\Attribute\AsTwigFunction;

/**
 * The editable site-wide values (page title, contacts, logos ...) of the "unique" admin groups, by their slugs.
 */
class UniqueContent
{
    /**
     * @var array<string, string>|null
     */
    private ?array $contents = null;

    public function __construct(private readonly UniqueItemRepository $items)
    {
    }

    #[AsTwigFunction('uniqueContent')]
    public function get(string $slug): string
    {
        $this->contents ??= $this->items->findAllContents();

        return $this->contents[$slug] ?? '';
    }
}
