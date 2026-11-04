<?php

namespace App\Tests\Controller\Admin;

use App\Tests\Support\AdminClientTrait;
use App\Tests\Support\PublicSiteFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * The pages the menu editor offers for the target of a menu item.
 */
final class MenuPageControllerTest extends WebTestCase
{
    use AdminClientTrait;
    use ResetDatabase;

    private KernelBrowser $client;
    private PublicSiteFixtures $fixtures;

    protected function setUp(): void
    {
        $this->client = $this->adminClient();
        $this->fixtures = new PublicSiteFixtures(self::getContainer()->get(EntityManagerInterface::class));
        $this->fixtures->create();
    }

    public function testOnlyTheAdminAsksForPages(): void
    {
        $this->client->getCookieJar()->clear();
        $this->client->request('GET', '/admin/menu-pages', ['term' => 'rendezv']);

        self::assertResponseRedirects('http://localhost/admin/login');
    }

    public function testTheMainListsAreFoundByTheirTitleWithoutAccents(): void
    {
        self::assertContains(
            ['title' => 'Népművészet', 'url' => '/nepmuveszet', 'kind' => 'Oldal'],
            $this->pages('NEPMUVESZ')
        );
        self::assertContains(
            ['title' => 'Rendezvények', 'url' => '/rendezvenyek', 'kind' => 'Oldal'],
            $this->pages('rendezvenyek')
        );
    }

    public function testTheArticleGroupsAndTheInformationPagesAreFound(): void
    {
        self::assertContains(
            ['title' => 'Hírek', 'url' => '/cikkek/hirek', 'kind' => 'Cikkcsoport'],
            $this->pages('hirek')
        );
        self::assertContains(
            ['title' => 'Rólunk', 'url' => '/informacio/rolunk', 'kind' => 'Információs oldal'],
            $this->pages('rolunk')
        );
    }

    public function testTheFolkArtAndTheChurchesAreNotOfferedAsArticleGroups(): void
    {
        $urls = array_column($this->pages('nepmuveszet'), 'url');

        self::assertContains('/nepmuveszet', $urls);
        self::assertNotContains('/cikkek/nepmuveszet', $urls);
        self::assertNotContains('/cikkek/egyhazak', array_column($this->pages('egyhazak'), 'url'));
    }

    public function testTheContentsOfTheSearchAreFound(): void
    {
        self::assertContains(
            ['title' => 'Kossuth-szobor', 'url' => '/emlekhelyek/szobor/kossuth-szobor', 'kind' => 'Emlékhely'],
            $this->pages('kossuth')
        );
        self::assertContains(
            [
                'title' => 'Magyar kultúra napja',
                'url' => '/rendezvenyek/unnep-unnepseg/magyar-kultura-napja',
                'kind' => 'Esemény',
            ],
            $this->pages('kultura napja')
        );
        self::assertContains(
            ['title' => 'Próba egyesület', 'url' => '/szervezetek/proba-egyesulet', 'kind' => 'Szervezet'],
            $this->pages('egyesulet')
        );
    }

    public function testTheGalleriesAndTheCitiesAreFound(): void
    {
        self::assertContains(
            ['title' => 'Próba galéria', 'url' => '/galeria/proba-galeria', 'kind' => 'Galéria'],
            $this->pages('galeria')
        );
        self::assertContains(
            [
                'title' => 'Beregszász',
                'url' => '/regiok/telepulesek/beregszaszi-jaras/beregszasz',
                'kind' => 'Település',
            ],
            $this->pages('beregsz')
        );
    }

    public function testAtMostTwelvePagesAreOffered(): void
    {
        for ($number = 1; $number <= 15; ++$number) {
            $this->fixtures->addNews('Tesztelő hír ' . $number);
        }
        self::getContainer()->get(EntityManagerInterface::class)->flush();

        self::assertCount(12, $this->pages('tesztelő'));
    }

    public function testAShortTermOrNoTermOffersNothing(): void
    {
        self::assertSame([], $this->pages('a'));
        self::assertSame([], $this->pages(''));
        self::assertSame([], $this->pages('  '));

        $this->client->request('GET', '/admin/menu-pages');
        self::assertResponseIsSuccessful();
        self::assertSame('[]', $this->client->getResponse()->getContent());
    }

    public function testATermThatIsNotTextIsABadRequest(): void
    {
        $this->client->request('GET', '/admin/menu-pages?term[]=hirek');

        self::assertResponseStatusCodeSame(400);
    }

    /**
     * @return list<array{title: string, url: string, kind: string}>
     */
    private function pages(string $term): array
    {
        $this->client->request('GET', '/admin/menu-pages', ['term' => $term]);
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');

        /** @var list<array{title: string, url: string, kind: string}> $pages */
        $pages = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

        return $pages;
    }
}
