<?php

namespace App\Twig\Components;

use App\Service\FooterFeeds as Feeds;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * The feeds of the partner institutes in the footer.
 *
 * @phpstan-import-type Feed from Feeds
 */
#[AsTwigComponent]
class FooterFeeds
{
    public function __construct(private readonly Feeds $feeds)
    {
    }

    /**
     * @return list<Feed>
     */
    public function getFeeds(): array
    {
        return $this->feeds->load();
    }
}
