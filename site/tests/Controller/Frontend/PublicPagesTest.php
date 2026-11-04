<?php

namespace App\Tests\Controller\Frontend;

use App\Tests\Support\PublicSiteFixtures;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Every public page of the site answers, with one H1 and with headings that do not skip a level (see the global
 * rules: the heading levels follow the content, the look is a matter of CSS).
 */
final class PublicPagesTest extends WebTestCase
{
    use ResetDatabase;

    private KernelBrowser $client;

    /**
     * @var array<string, string>
     */
    private array $urls;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->urls = (new PublicSiteFixtures(self::getContainer()->get(EntityManagerInterface::class)))->create();
    }

    public function testEveryPageAnswers(): void
    {
        foreach ($this->urls as $name => $url) {
            $crawler = $this->client->request('GET', $url);

            self::assertResponseIsSuccessful(sprintf('The page "%s" (%s) must answer.', $name, $url));
            self::assertSame(1, $crawler->filter('h1')->count(), sprintf('The page "%s" must have one H1.', $name));
            self::assertSame($this->headingLevelsOf($crawler), $this->withoutLevelSkips($crawler), $name);
        }
    }

    public function testHomepageShowsTheFrameAndTheBlocks(): void
    {
        $crawler = $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSame('KMMI | Kárpátaljai Magyar Művelődési Intézet', $crawler->filter('title')->text());
        self::assertSame(['Hírek', 'Adattár', 'Rendezvények'], $crawler->filter('.feature-menu > li > a')->each(
            static fn (Crawler $link): string => trim($link->text())
        ));
        self::assertCount(1, $crawler->filter('.carousel-item'));
        self::assertStringContainsString('/media/cache/', (string) $crawler->filter('.carousel-item img')->attr('src'));
        self::assertGreaterThan(0, $crawler->filter('.dropdown-menu .dropdown-item')->count());
        self::assertSame(['Hírek', 'Emlékhelyek', 'Események', 'Képtár'], $crawler->filter('.body-content h2')->each(
            static fn (Crawler $title): string => trim($title->text())
        ));
        self::assertStringContainsString(
            '2024. március 05. - 2024. március 07.',
            $crawler->filter('.body-content')->text()
        );
        self::assertSame('Copyright © ' . date('Y') . ' KMMI', $crawler->filter('.impress .text-center')->text());
    }

    public function testTheTitlesKeepTheirEntitiesButTheMetaTagsAreEscaped(): void
    {
        $crawler = $this->client->request('GET', $this->urls['article']);

        self::assertStringContainsString('Hírek – cikk', $crawler->filter('h1')->text());
        self::assertStringContainsString('Hírek – cikk | Hírek | ', $crawler->filter('title')->text());
        self::assertSame('Bevezető szöveg.', $crawler->filter('meta[name=description]')->attr('content'));
    }

    public function testTheLegacyUrlsAreServedAsTheyWere(): void
    {
        self::assertSame('/cikkek/hirek/hirek-8211-cikk', $this->urls['article']);
        self::assertSame('/nepmuveszet/nepmuveszet-8211-cikk', $this->urls['folk art article']);
        self::assertSame('/rendezvenyek/unnep-unnepseg/magyar-kultura-napja', $this->urls['event']);
        self::assertSame('/emlekhelyek/szobor/kossuth-szobor', $this->urls['monument']);
        self::assertSame('/regiok/telepulesek/beregszaszi-jaras/beregszasz', $this->urls['city']);
        self::assertSame('/szervezetek/proba-egyesulet', $this->urls['organization']);
    }

    public function testTheAboutPagesListTheDepartments(): void
    {
        $crawler = $this->client->request('GET', '/informacio/kozgyujtemenyi-reszleg');

        self::assertSame('Részlegek, elérhetőségek', $crawler->filter('.related h2')->text());
        self::assertSame(['Közgyűjteményi részleg'], $crawler->filter('.related h3')->each(
            static fn (Crawler $item): string => trim($item->text())
        ));
        self::assertSame('Közgyűjteményi részleg', $crawler->filter('.related a.active')->text());

        // The other documents have the latest news in their sidebar instead.
        $crawler = $this->client->request('GET', '/informacio/partnereink');
        self::assertCount(0, $crawler->filter('.related'));
        self::assertContains('Hírek', $crawler->filter('.body-content h2')->each(
            static fn (Crawler $title): string => trim($title->text())
        ));
    }

    public function testALibraryItemKnowsItsCategoryAndAuthor(): void
    {
        $crawler = $this->client->request('GET', '/konyvtar/egyutt/reszletes/egy-szam');

        // The dividers of the crumbs are drawn by the CSS, they are not in the text.
        self::assertSame(
            'Kezdőlap Adattár Könyvtár Folyóiratok Együtt Egy szám',
            preg_replace('/\s+/', ' ', $crawler->filter('.breadcrumb')->text())
        );
        self::assertSame('Írta: Szerző Sz. | A kiadás éve: 2019', $crawler->filter('.article-update-date')->text());

        $crawler = $this->client->request('GET', '/konyvtar/folyoiratok/egyutt');
        self::assertSame(
            '/konyvtar/egyutt/reszletes/egy-szam',
            $crawler->filter('ul.list-unstyled h2 a')->attr('href')
        );
    }

    public function testThePlayersTellTheTypeOfTheFile(): void
    {
        $crawler = $this->client->request('GET', '/hangtar/egy-hang');
        self::assertSame('audio/mpeg', $crawler->filter('audio source')->attr('type'));

        $crawler = $this->client->request('GET', '/videotar/egy-video');
        self::assertSame('video/x-flv', $crawler->filter('video source')->attr('type'));
        self::assertSame('/uploads/attachments/video/a.flv', $crawler->filter('video a')->attr('href'));
    }

    public function testAPageOfTheListsNeverFails(): void
    {
        foreach (['0', '-3', 'abc', '999'] as $page) {
            $this->client->request('GET', '/cikkek/hirek?page=' . $page);

            self::assertResponseIsSuccessful('page=' . $page);
        }
        self::assertSelectorTextContains('.list-unstyled', 'Nincs megjeleníthető tartalom');
    }

    public function testATrailingSlashIsRedirected(): void
    {
        $this->client->request('GET', '/cikkek/hirek/');

        self::assertResponseRedirects('/cikkek/hirek', 301);
    }

    #[DataProvider('unknownPages')]
    public function testUnknownContentIsNotFound(string $url): void
    {
        $this->client->request('GET', $url);

        self::assertResponseStatusCodeSame(404);
    }

    public function testTheNotFoundPageIsAPageOfTheSite(): void
    {
        // Without debug the error controller renders the page of the site, as in production.
        self::ensureKernelShutdown();
        $crawler = self::createClient(['debug' => false])->request('GET', '/nincs-ilyen-oldal');

        self::assertResponseStatusCodeSame(404);
        self::assertSame('A keresett oldal nem található', $crawler->filter('h1')->text());
        self::assertCount(1, $crawler->filter('h1'));
        self::assertGreaterThan(0, $crawler->filter('.feature-menu .nav-link')->count());
        self::assertSame(['Események', 'Hírek', 'Képtár'], $crawler->filter('.body-content h2')->each(
            static fn (Crawler $title): string => trim($title->text())
        ));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function unknownPages(): iterable
    {
        yield 'article type' => ['/cikkek/nincs-ilyen'];
        yield 'article' => ['/cikkek/hirek/nincs-ilyen'];
        yield 'folk art' => ['/nepmuveszet/nincs-ilyen'];
        yield 'event type' => ['/rendezvenyek/nincs-ilyen'];
        yield 'event' => ['/rendezvenyek/unnep-unnepseg/nincs-ilyen'];
        yield 'monument' => ['/emlekhelyek/szobor/nincs-ilyen'];
        yield 'gallery' => ['/galeria/nincs-ilyen'];
        yield 'city' => ['/regiok/telepulesek/beregszaszi-jaras/nincs-ilyen'];
        yield 'organization' => ['/szervezetek/nincs-ilyen'];
        yield 'document' => ['/informacio/nincs-ilyen'];
        yield 'library category' => ['/konyvtar/nincs-ilyen'];
        yield 'library subcategory' => ['/konyvtar/folyoiratok/nincs-ilyen'];
        yield 'library item' => ['/konyvtar/konyvek/reszletes/nincs-ilyen'];
        yield 'library item of an unknown category' => ['/konyvtar/nincs-ilyen/reszletes/egy-konyv'];
        yield 'actor type' => ['/arckepcsarnok/nincs-ilyen'];
        yield 'actor' => ['/arckepcsarnok/muveszek/nincs-ilyen'];
        yield 'education type' => ['/oktatasi-intezmenyek/nincs-ilyen'];
        yield 'audio' => ['/hangtar/nincs-ilyen'];
        yield 'video' => ['/videotar/nincs-ilyen'];
    }

    /**
     * @return list<int>
     */
    private function headingLevelsOf(Crawler $crawler): array
    {
        return $crawler->filter('h1, h2, h3, h4, h5, h6')->each(
            static fn (Crawler $heading): int => (int) substr($heading->nodeName(), 1)
        );
    }

    /**
     * The levels of the page, each one at most one deeper than the one before (the first is the H1).
     *
     * @return list<int>
     */
    private function withoutLevelSkips(Crawler $crawler): array
    {
        $levels = [];
        $previous = 0;
        foreach ($this->headingLevelsOf($crawler) as $level) {
            $previous = min($level, $previous + 1);
            $levels[] = $previous;
        }

        return $levels;
    }
}
