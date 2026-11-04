<?php

namespace App\Tests\Controller\Frontend;

use App\Tests\Support\PublicSiteFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * The search page of the header form: /search/frontend?search_form[term]=...
 */
final class SearchPageTest extends WebTestCase
{
    use ResetDatabase;

    private KernelBrowser $client;
    private PublicSiteFixtures $fixtures;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->fixtures = new PublicSiteFixtures($this->entityManager);
        $this->fixtures->create();
    }

    public function testTheResultsAreListedLikeTheOtherLists(): void
    {
        $crawler = $this->search('szobor');

        self::assertResponseIsSuccessful();
        self::assertSame('Keresés - KMMI | Kárpátaljai Magyar Művelődési Intézet', $crawler->filter('title')->text());
        self::assertSame('noindex, follow', $crawler->filter('meta[name=robots]')->attr('content'));
        self::assertSame(['szobor (1 db.)'], $this->texts($crawler, 'h1'));
        self::assertSame('h1', $crawler->filter('h1, h2, h3')->first()->nodeName(), 'The H1 is the first heading.');
        self::assertSame('Kezdőlap Keresés szobor', $this->crumbs($crawler));

        self::assertSame(['Kossuth-szobor'], $this->texts($crawler, 'ul.list-unstyled h2 a'));
        self::assertSame(
            '/emlekhelyek/szobor/kossuth-szobor',
            $crawler->filter('ul.list-unstyled h2 a')->attr('href')
        );
        self::assertSame(['Az emlékmű leírása.'], $this->texts($crawler, 'ul.list-unstyled li p'));
        self::assertCount(3, $crawler->filter('.col-md-4 h2'), 'The sidebar: the news, the events, the galleries.');
    }

    public function testTheHeaderFormSendsTheTermToTheSearchPage(): void
    {
        $crawler = $this->client->request('GET', '/');

        self::assertSame('/search/frontend', $crawler->filter('form#search-form')->attr('action'));
        self::assertSame('get', $crawler->filter('form#search-form')->attr('method'));
        self::assertCount(1, $crawler->filter('form#search-form input[name="search_form[term]"]'));
    }

    public function testWithoutATermThePageSaysSo(): void
    {
        $crawler = $this->client->request('GET', '/search/frontend');

        self::assertResponseIsSuccessful();
        self::assertSame(['Nincs keresőszó megadva'], $this->texts($crawler, 'h1'));
        self::assertSame('Kezdőlap Keresés', $this->crumbs($crawler));
        self::assertCount(0, $crawler->filter('ul.list-unstyled li'));
        self::assertCount(0, $crawler->filter('.navigation'));
    }

    public function testATermThatIsNotTextIsNoTerm(): void
    {
        foreach (['search_form=szobor', 'search_form[term][]=szobor', 'search_form[term]=%20%20'] as $query) {
            $crawler = $this->client->request('GET', '/search/frontend?' . $query);

            self::assertResponseIsSuccessful($query);
            self::assertSame(['Nincs keresőszó megadva'], $this->texts($crawler, 'h1'), $query);
        }
    }

    public function testATermWithoutHitsHasNoList(): void
    {
        $crawler = $this->search('nincsilyenszo');

        self::assertResponseIsSuccessful();
        self::assertSame(['nincsilyenszo (0 db.)'], $this->texts($crawler, 'h1'));
        self::assertCount(0, $crawler->filter('ul.list-unstyled li'));
    }

    public function testTheTermIsEscaped(): void
    {
        $crawler = $this->search('<script>alert(1)</script>');

        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringNotContainsString('<script>alert(1)</script>', $content);
        self::assertSame(['<script>alert(1)</script> (0 db.)'], $this->texts($crawler, 'h1'));
        self::assertSame('Kezdőlap Keresés <script>alert(1)</script>', $this->crumbs($crawler));
    }

    public function testTheHitsAreOnPagesOfTwelve(): void
    {
        for ($number = 1; $number <= 14; ++$number) {
            $this->fixtures->addNews('Lapozó hír ' . $number);
        }
        $this->entityManager->flush();

        $crawler = $this->search('lapozó');
        self::assertSame(['lapozó (14 db.)'], $this->texts($crawler, 'h1'));
        self::assertCount(12, $crawler->filter('ul.list-unstyled > li'));
        $next = $crawler->filter('.pagination a[rel=next]')->attr('href');
        self::assertStringContainsString('search_form%5Bterm%5D=lap', (string) $next);
        self::assertStringContainsString('page=2', (string) $next);

        $crawler = $this->client->request('GET', (string) $next);
        self::assertCount(2, $crawler->filter('ul.list-unstyled > li'));
        self::assertCount(0, $crawler->filter('.pagination a[rel=next]'));

        // A page that is not a number is the first one, a page after the last one is empty.
        self::assertCount(12, $this->search('lapozó', ['page' => 'abc'])->filter('ul.list-unstyled > li'));
        self::assertCount(12, $this->search('lapozó', ['page' => '-5'])->filter('ul.list-unstyled > li'));
        self::assertCount(0, $this->search('lapozó', ['page' => '99'])->filter('ul.list-unstyled > li'));
        self::assertResponseIsSuccessful();
    }

    public function testTheSortParametersOfThePagerDoNotBreakThePage(): void
    {
        $crawler = $this->search('szobor', ['sort' => 'title', 'direction' => 'desc']);

        self::assertResponseIsSuccessful();
        self::assertSame(['Kossuth-szobor'], $this->texts($crawler, 'ul.list-unstyled h2 a'));
    }

    /**
     * @param array<string, string> $more
     */
    private function search(string $term, array $more = []): Crawler
    {
        return $this->client->request('GET', '/search/frontend', ['search_form' => ['term' => $term]] + $more);
    }

    /**
     * @return list<string>
     */
    private function texts(Crawler $crawler, string $selector): array
    {
        return $crawler->filter($selector)->each(static fn (Crawler $node): string => trim($node->text()));
    }

    private function crumbs(Crawler $crawler): string
    {
        return (string) preg_replace('/\s+/', ' ', trim($crawler->filter('.breadcrumb')->text()));
    }
}
