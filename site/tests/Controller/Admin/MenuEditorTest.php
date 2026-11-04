<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Menu\Menu;
use App\Entity\Menu\MenuItem;
use App\Tests\Support\AdminClientTrait;
use App\Tests\Support\PublicSiteFixtures;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * The menu editor of the admin: the items as a tree of three levels, their order and their targets are saved as they
 * are on the screen, and what is saved is what the public menu shows.
 *
 * A tree is a list of [name, target, sub items] (the menu of the fixtures is ORIGINAL).
 */
final class MenuEditorTest extends WebTestCase
{
    use AdminClientTrait;
    use ResetDatabase;

    private const EDIT = '/admin/menu/2/edit';

    private const ORIGINAL = [
        ['Hírek', '/cikkek/hirek', []],
        ['Adattár', '#', [
            ['Könyvtár', '/konyvtar/konyvek', [
                ['Folyóiratok', '/konyvtar/folyoiratok/egyutt', []],
            ]],
            ['Képtár', '/kepek', []],
        ]],
        ['Rendezvények', '/rendezvenyek', []],
    ];

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = $this->adminClient();
        (new PublicSiteFixtures($this->entityManager()))->create();
        // The first request would see the menu as it was built (without its items), the next ones read the database.
        $this->entityManager()->clear();
    }

    public function testTheMenusAreListedButNotCreatedOrDeleted(): void
    {
        $crawler = $this->client->request('GET', '/admin/menu');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Főmenu - HU', $crawler->filter('table')->text());
        self::assertCount(0, $crawler->filter('.action-new'), 'The menus are created in the database.');
        self::assertCount(0, $crawler->filter('.action-delete'));
    }

    public function testTheEditorShowsTheItemsAsATree(): void
    {
        $crawler = $this->client->request('GET', self::EDIT);

        self::assertResponseIsSuccessful();
        $items = $crawler->filter('.kmmi-menu-item')->each(
            static fn (Crawler $item): array => [
                (int) $item->attr('data-level'),
                $item->filter('.kmmi-menu-row')->first()->filter('[data-menu-field="title"]')->attr('value'),
                $item->filter('.kmmi-menu-row')->first()->filter('[data-menu-field="url"]')->attr('value'),
            ]
        );
        self::assertSame([
            [1, 'Hírek', '/cikkek/hirek'],
            [1, 'Adattár', '#'],
            [2, 'Könyvtár', '/konyvtar/konyvek'],
            [3, 'Folyóiratok', '/konyvtar/folyoiratok/egyutt'],
            [2, 'Képtár', '/kepek'],
            [1, 'Rendezvények', '/rendezvenyek'],
        ], $items);

        // The items of the first two levels have a list for their sub items (it is there for the items that are
        // dropped into it), the items of the last level have none.
        self::assertCount(0, $crawler->filter('.kmmi-menu-item[data-level="3"] .kmmi-menu-list'));
        self::assertCount(5, $crawler->filter(
            '.kmmi-menu-item[data-level="1"] > .kmmi-menu-list, .kmmi-menu-item[data-level="2"] > .kmmi-menu-list'
        ));
    }

    public function testTheEditorKnowsHowToAddItemsOfEveryLevelAndFindPages(): void
    {
        $crawler = $this->client->request('GET', self::EDIT);

        $tree = $crawler->filter('.kmmi-menu-tree');
        self::assertCount(1, $tree);
        self::assertSame('Menu[menuItems]', $tree->attr('data-name'));
        self::assertSame('3', $tree->attr('data-max-level'));
        self::assertNotSame('', (string) $tree->attr('data-confirm'));
        foreach ([1, 2, 3] as $level) {
            $prototype = (string) $tree->attr('data-prototype-' . $level);
            self::assertStringContainsString('<li class="kmmi-menu-item" data-level="' . $level . '">', $prototype);
            self::assertStringContainsString('data-menu-field="title"', $prototype);
            self::assertStringContainsString('data-kmmi-page-search="/admin/menu-pages"', $prototype);
        }
        self::assertSame(1, $tree->filter('.kmmi-menu-add')->count());
        self::assertSame(
            'Keresés vagy URL',
            $tree->filter('[data-menu-field="url"]')->first()->attr('placeholder')
        );
    }

    public function testTheTreeIsSavedAsItIsOnTheScreen(): void
    {
        $this->save([
            // "Adattár" moves to the top with its sub items turned around; "Könyvtár" is renamed and gets a sub item.
            ['Adattár', '#', [
                ['Képtár', '/kepek', []],
                ['Könyvtár (új név)', '/konyvtar/konyvek', [
                    ['Folyóiratok', '/konyvtar/folyoiratok/egyutt', []],
                    ['Új folyóirat', ' /konyvtar/folyoiratok/beregi-hirlap ', []],
                ]],
            ]],
            ['Rendezvények', '/rendezvenyek', []],
            // "Hírek" goes to the end and has a sub item now; a new item with a sub item is at the end of the screen.
            ['Hírek', '/cikkek/hirek', [['Újdonság', '/cikkek/hirek/ujdonsag', []]]],
            ['Rólunk', '/informacio/rolunk', [['Partnereink', '/informacio/partnereink', []]]],
        ]);

        self::assertSame([
            ['Adattár', '#', [
                ['Képtár', '/kepek', []],
                ['Könyvtár (új név)', '/konyvtar/konyvek', [
                    ['Folyóiratok', '/konyvtar/folyoiratok/egyutt', []],
                    ['Új folyóirat', '/konyvtar/folyoiratok/beregi-hirlap', []],
                ]],
            ]],
            ['Rendezvények', '/rendezvenyek', []],
            ['Hírek', '/cikkek/hirek', [['Újdonság', '/cikkek/hirek/ujdonsag', []]]],
            ['Rólunk', '/informacio/rolunk', [['Partnereink', '/informacio/partnereink', []]]],
        ], $this->savedTree());

        $crawler = $this->client->request('GET', '/');
        self::assertSame(
            ['Adattár', 'Rendezvények', 'Hírek', 'Rólunk'],
            $crawler->filter('.feature-menu > li > a')->each(static fn (Crawler $link): string => trim($link->text()))
        );
    }

    public function testAnItemCanMoveToAnotherLevel(): void
    {
        $this->save([
            ['Hírek', '/cikkek/hirek', []],
            ['Képtár', '/kepek', []],
            ['Adattár', '#', [['Folyóiratok', '/konyvtar/folyoiratok/egyutt', []]]],
            ['Rendezvények', '/rendezvenyek', [['Könyvtár', '/konyvtar/konyvek', []]]],
        ]);

        self::assertSame([
            ['Hírek', '/cikkek/hirek', []],
            ['Képtár', '/kepek', []],
            ['Adattár', '#', [['Folyóiratok', '/konyvtar/folyoiratok/egyutt', []]]],
            ['Rendezvények', '/rendezvenyek', [['Könyvtár', '/konyvtar/konyvek', []]]],
        ], $this->savedTree());
    }

    public function testRemovingAnItemRemovesItsSubItems(): void
    {
        $this->save([['Hírek', '/cikkek/hirek', []], ['Rendezvények', '/rendezvenyek', []]]);

        self::assertSame(
            [['Hírek', '/cikkek/hirek', []], ['Rendezvények', '/rendezvenyek', []]],
            $this->savedTree()
        );
        self::assertSame(2, $this->entityManager()->getRepository(MenuItem::class)->count([]), 'The rows are deleted.');
    }

    public function testAnUnchangedFormKeepsTheTree(): void
    {
        $crawler = $this->client->request('GET', self::EDIT);
        $values = $crawler->selectButton('Mentés')->form()->getPhpValues();
        $this->client->request('POST', self::EDIT, $values);
        self::assertResponseRedirects();

        $this->entityManager()->clear();
        self::assertSame(self::ORIGINAL, $this->savedTree());
    }

    public function testThereIsNoFourthLevel(): void
    {
        $values = $this->formValues();
        $values['Menu']['menuItems'] = $this->rows(self::ORIGINAL);
        $values['Menu']['menuItems'][1]['children'][0]['children'][0]['children'][0] = [
            'title' => 'Túl mély',
            'url' => '/mely',
        ];

        $this->client->request('POST', self::EDIT, $values);

        self::assertResponseStatusCodeSame(422);
        $this->entityManager()->clear();
        self::assertSame(self::ORIGINAL, $this->savedTree());
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function invalidItems(): iterable
    {
        yield 'no name' => ['', '/konyvtar/folyoiratok/egyutt', 'Ez az érték nem lehet üres'];
        yield 'no target' => ['Folyóiratok', '', 'Ez az érték nem lehet üres'];
        yield 'a path without the leading slash' => ['Folyóiratok', 'konyvtar/folyoiratok', 'A cél a # jel'];
        yield 'a script' => ['Folyóiratok', 'javascript:alert(1)', 'A cél a # jel'];
        yield 'an address of the current protocol' => ['Folyóiratok', '//example.test/', 'A cél a # jel'];
    }

    #[DataProvider('invalidItems')]
    public function testAnInvalidItemIsNotSavedWhateverLevelItIsOn(string $title, string $url, string $error): void
    {
        $tree = self::ORIGINAL;
        $tree[1][2][0][2][0] = [$title, $url, []];
        $values = $this->formValues();
        $values['Menu']['menuItems'] = $this->rows($tree);

        $crawler = $this->client->request('POST', self::EDIT, $values);

        self::assertResponseStatusCodeSame(422);
        self::assertStringContainsString($error, $crawler->filter('.kmmi-menu-tree')->text());
        $this->entityManager()->clear();
        self::assertSame(self::ORIGINAL, $this->savedTree());
    }

    public function testAnAddressIsATarget(): void
    {
        $tree = self::ORIGINAL;
        $tree[0][1] = 'https://example.test/hirek?a=1#b';
        $this->save($tree);

        self::assertSame('https://example.test/hirek?a=1#b', $this->savedTree()[0][1]);
    }

    /**
     * Saves a tree as the editor would send it.
     *
     * @param list<array{string, string, list<mixed>}> $tree
     */
    private function save(array $tree): void
    {
        $values = $this->formValues();
        $values['Menu']['menuItems'] = $this->rows($tree);
        $this->client->request('POST', self::EDIT, $values);
        self::assertResponseRedirects();
    }

    /**
     * @param list<array{string, string, list<mixed>}> $tree
     *
     * @return list<array<string, mixed>>
     */
    private function rows(array $tree): array
    {
        return array_map(
            fn (array $item): array => ['title' => $item[0], 'url' => $item[1]]
                + ($item[2] === [] ? [] : ['children' => $this->rows($item[2])]),
            $tree
        );
    }

    /**
     * @return list<array{string, string|null, list<mixed>}>
     */
    private function savedTree(): array
    {
        $this->entityManager()->clear();
        $menu = $this->entityManager()->find(Menu::class, 2) ?? self::fail('The menu is gone.');

        return $this->treeOf($menu->getMenuItems());
    }

    /**
     * @param iterable<MenuItem> $items
     *
     * @return list<array{string, string|null, list<mixed>}>
     */
    private function treeOf(iterable $items): array
    {
        $tree = [];
        foreach ($items as $item) {
            $tree[] = [(string) $item->getTitle(), $item->getUrl(), $this->treeOf($item->getChildren())];
        }

        return $tree;
    }

    /**
     * @return array<string, mixed>
     */
    private function formValues(): array
    {
        $crawler = $this->client->request('GET', self::EDIT);
        self::assertResponseIsSuccessful();

        return $crawler->selectButton('Mentés')->form()->getPhpValues();
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }
}
