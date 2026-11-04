<?php

namespace App\Twig\Components;

use App\Entity\Menu\MenuItem;
use App\Repository\Menu\MenuItemRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * The main menu of the header: the items of the menu "Főmenu - HU" (id 2) and, under the item "Adattár" (its URL
 * is "#"), the mega menu of the data pages. The URLs are the public URLs of the site (see AGENTS.md).
 *
 * @phpstan-type Link array{label: string, url?: string, links?: list<array{label: string, url: string}>}
 * @phpstan-type Column array{title: string, icon: string, links: list<Link>}
 */
#[AsTwigComponent]
class MainMenu
{
    private const MENU_ID = 2;

    /**
     * @var list<Column>
     */
    private const DATA_MENU = [
        [
            'title' => 'Könyvtár',
            'icon' => 'fa-book',
            'links' => [
                ['label' => 'Könyvek', 'url' => '/konyvtar/konyvek'],
                [
                    'label' => 'Folyóiratok',
                    'links' => [
                        ['label' => 'Együtt', 'url' => '/konyvtar/folyoiratok/egyutt'],
                        ['label' => 'Kárpátalja (Miskolc)', 'url' => '/konyvtar/folyoiratok/karpatalja-miskolc'],
                        ['label' => 'Beregi Hírlap', 'url' => '/konyvtar/folyoiratok/beregi-hirlap'],
                        ['label' => 'Kárpáti Krónika', 'url' => '/konyvtar/folyoiratok/karpati-kronika'],
                        ['label' => 'Kárpátaljai Hírmondó', 'url' => '/konyvtar/folyoiratok/karpataljai-hirmondo'],
                    ],
                ],
                ['label' => 'Demográfia', 'url' => '/konyvtar/demografia'],
                ['label' => 'Bibliográfia', 'url' => '/konyvtar/bibliografia'],
                ['label' => 'Hírlevelek', 'url' => '/konyvtar/egyeb'],
                ['label' => 'KMMI-füzetek', 'url' => '/konyvtar/kmmi-fuzetek'],
            ],
        ],
        [
            'title' => 'Multimédia',
            'icon' => 'fa-play',
            'links' => [
                ['label' => 'Képtár', 'url' => '/kepek'],
                ['label' => 'Videótár', 'url' => '/videotar'],
                ['label' => 'Hangtár', 'url' => '/hangtar'],
            ],
        ],
        [
            'title' => 'Oktatási intézmények',
            'icon' => 'fa-university',
            'links' => [
                ['label' => 'Általános iskolák', 'url' => '/oktatasi-intezmenyek/altalanos-iskolak'],
                ['label' => 'Bölcsödék', 'url' => '/oktatasi-intezmenyek/bolcsodek'],
                ['label' => 'Egyetemek', 'url' => '/oktatasi-intezmenyek/egyetemek'],
                ['label' => 'Elemi iskolák', 'url' => '/oktatasi-intezmenyek/elemi-iskolak'],
                ['label' => 'Főiskolák', 'url' => '/oktatasi-intezmenyek/foiskolak'],
                ['label' => 'Gimnáziumok', 'url' => '/oktatasi-intezmenyek/gimnaziumok'],
                ['label' => 'Középiskolák', 'url' => '/oktatasi-intezmenyek/kozepiskolak'],
                ['label' => 'Líceumok', 'url' => '/oktatasi-intezmenyek/liceum'],
                ['label' => 'Oktatási-Nevelési Központ', 'url' => '/oktatasi-intezmenyek/oktatasi-nevelesi-komplexum'],
                ['label' => 'Óvodák', 'url' => '/oktatasi-intezmenyek/ovodak'],
            ],
        ],
        [
            'title' => 'Arcképcsarnok',
            'icon' => 'fa-paint-brush',
            'links' => [
                ['label' => 'Népművészek', 'url' => '/arckepcsarnok/nepmuveszek'],
                ['label' => 'Művészek', 'url' => '/arckepcsarnok/muveszek'],
                ['label' => 'Írók/költők', 'url' => '/arckepcsarnok/irok-koltok'],
            ],
        ],
        [
            'title' => 'Egyéb',
            'icon' => 'fa-info-circle',
            'links' => [
                ['label' => 'Népművészet', 'url' => '/nepmuveszet'],
                ['label' => 'Egyházak', 'url' => '/egyhazak'],
                ['label' => 'Szervezetek', 'url' => '/szervezetek'],
                ['label' => 'Emlékhelyek', 'url' => '/emlekhelyek'],
                ['label' => 'Települések', 'url' => '/regiok/telepulesek'],
            ],
        ],
    ];

    public function __construct(private readonly MenuItemRepository $items)
    {
    }

    /**
     * @return list<MenuItem>
     */
    public function getItems(): array
    {
        return $this->items->findRootItems(self::MENU_ID);
    }

    /**
     * @return list<Column>
     */
    public function getDataMenu(): array
    {
        return self::DATA_MENU;
    }
}
