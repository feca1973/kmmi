<?php

namespace App\Tests\Controller\Seo;

use App\Tests\Support\PublicSiteFixtures;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * The sitemap lists exactly the pages that exist, and the files for the crawlers and the language models answer.
 */
final class SeoPagesTest extends WebTestCase
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

    public function testEveryUrlOfTheSitemapIsAnExistingPage(): void
    {
        $paths = $this->sitemapPaths();

        self::assertContains('/', $paths);
        foreach ($this->urls as $name => $url) {
            self::assertContains($url, $paths, sprintf('The page "%s" must be in the sitemap.', $name));
        }
        foreach ($paths as $path) {
            $this->client->request('GET', $path);
            self::assertResponseIsSuccessful(sprintf('The sitemap lists %s, it must answer.', $path));
        }
    }

    public function testTheSitemapHasTheLegacyUrlsOnly(): void
    {
        $expected = [
            '/',
            '/arckepcsarnok/muveszek',
            '/arckepcsarnok/muveszek/egy-muvesz',
            '/cikkek/egyhazak',
            '/cikkek/hirek',
            '/cikkek/hirek/hirek-8211-cikk',
            '/cikkek/nepmuveszet',
            '/egyhazak',
            '/emlekhelyek',
            '/emlekhelyek/szobor/kossuth-szobor',
            '/galeria/proba-galeria',
            '/hangtar',
            '/hangtar/egy-hang',
            '/informacio/kozgyujtemenyi-reszleg',
            '/informacio/partnereink',
            '/informacio/rolunk',
            '/kepek',
            '/konyvtar/egyutt/reszletes/egy-szam',
            '/konyvtar/folyoiratok/egyutt',
            '/konyvtar/konyvek',
            '/konyvtar/konyvek/reszletes/egy-konyv',
            '/nepmuveszet',
            '/nepmuveszet/nepmuveszet-8211-cikk',
            '/oktatasi-intezmenyek/ovodak',
            '/regiok/telepulesek',
            '/regiok/telepulesek/beregszaszi-jaras/beregszasz',
            '/rendezvenyek',
            '/rendezvenyek/unnep-unnepseg',
            '/rendezvenyek/unnep-unnepseg/magyar-kultura-napja',
            '/szervezetek',
            '/szervezetek/proba-egyesulet',
            '/videotar',
            '/videotar/egy-video',
        ];
        $paths = $this->sitemapPaths();

        sort($expected);
        sort($paths);
        self::assertSame($expected, $paths);
    }

    public function testTheSitemapIndexAndTheFilesAreXml(): void
    {
        $this->client->request('GET', '/sitemap.xml');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('application/xml', $this->header('Content-Type'));
        self::assertStringContainsString('<loc>http://localhost/sitemap-articles.xml</loc>', $this->body());

        $this->client->request('GET', '/sitemap-nincs.xml');
        self::assertResponseStatusCodeSame(404);
    }

    public function testRobotsTxt(): void
    {
        $this->client->request('GET', '/robots.txt');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('text/plain', $this->header('Content-Type'));
        self::assertStringContainsString('Sitemap: http://localhost/sitemap.xml', $this->body());
    }

    public function testTheGeneratedFilesAreCachedAndRevalidated(): void
    {
        $this->client->request('GET', '/sitemap.xml');
        $etag = (string) $this->client->getResponse()->headers->get('ETag');

        self::assertNotSame('', $etag);
        self::assertStringContainsString('public', $this->header('Cache-Control'));

        $this->client->request('GET', '/sitemap.xml', server: ['HTTP_IF_NONE_MATCH' => $etag]);
        self::assertResponseStatusCodeSame(304);
    }

    public function testLlmsTxtTellsWhatTheSiteIs(): void
    {
        $this->client->request('GET', '/llms.txt');
        $content = $this->body();

        self::assertResponseIsSuccessful();
        self::assertStringStartsWith('# Kárpátaljai Magyar Művelődési Intézet (KMMI)', $content);
        self::assertStringContainsString('## Fő oldalak', $content);
        self::assertStringContainsString('(http://localhost/llms-full.txt)', $content);
        self::assertStringContainsString('(http://localhost/cikkek/hirek/hirek-8211-cikk.md)', $content);
    }

    public function testLlmsFullHasThePermanentContentButNotTheArticles(): void
    {
        $this->client->request('GET', '/llms-full.txt');
        $content = $this->body();

        self::assertResponseIsSuccessful();
        $titles = [
            'Rólunk', 'Próba egyesület', 'Beregszász', 'Kossuth-szobor', 'Egy művész', 'Egy óvoda', 'Egy szám',
            'Magyar kultúra napja',
        ];
        foreach ($titles as $title) {
            self::assertStringContainsString('### ' . $title, $content);
        }
        self::assertStringContainsString('URL: http://localhost/szervezetek/proba-egyesulet', $content);
        self::assertStringNotContainsString('A cikk szövege', $content);
        // A library item is a catalog entry here: its text is on its own Markdown page.
        self::assertSame(1, preg_match('/\n## Könyvtár\n(.*?)(?=\n## |\z)/s', $content, $library));
        self::assertStringContainsString('Bevezető.', $library[1]);
        self::assertStringNotContainsString('Szöveg.', $library[1]);
    }

    public function testEveryArticleEventAndMonumentIsAvailableAsMarkdown(): void
    {
        $expected = [
            $this->urls['article'] => ['# Hírek – cikk', 'A cikk szövege.', '- Szerző: Szerző'],
            $this->urls['folk art article'] => ['# Népművészet – cikk', 'A cikk szövege.'],
            $this->urls['event'] => ['# Magyar kultúra napja', '- Időpont: 2024. március 05. - 2024. március 07.'],
            $this->urls['monument'] => ['# Kossuth-szobor', '- Település: Tecső', 'Az emlékmű leírása.'],
            $this->urls['about us'] => ['# Rólunk', 'Az oldal szövege.'],
            $this->urls['periodical issue'] => [
                '# Egy szám', '- Kategória: Folyóiratok / Együtt', '- Szerző: Szerző Sz.',
            ],
            $this->urls['artist'] => ['# Egy művész', '- Típus: Művészek', '- Kapcsolódó település: Beregszász'],
            $this->urls['organization'] => ['# Próba egyesület', 'Működési területe'],
            $this->urls['city'] => ['# Beregszász', '- Járás: Beregszászi járás'],
        ];

        foreach ($expected as $path => $fragments) {
            $this->client->request('GET', $path . '.md');
            $content = $this->body();

            self::assertResponseIsSuccessful($path . '.md');
            self::assertStringContainsString('text/markdown', $this->header('Content-Type'));
            self::assertStringContainsString('URL: http://localhost' . $path, $content);
            foreach ($fragments as $fragment) {
                self::assertStringContainsString($fragment, $content, $path . '.md');
            }
        }
    }

    public function testAnUnknownMarkdownPageIsNotFound(): void
    {
        $this->client->request('GET', '/cikkek/hirek/nincs-ilyen.md');

        self::assertResponseStatusCodeSame(404);
    }

    private function header(string $name): string
    {
        return (string) $this->client->getResponse()->headers->get($name);
    }

    private function body(): string
    {
        return (string) $this->client->getResponse()->getContent();
    }

    /**
     * @return list<string>
     */
    private function sitemapPaths(): array
    {
        $this->client->request('GET', '/sitemap.xml');
        preg_match_all('#<loc>http://localhost(/sitemap-[^<]+)</loc>#', $this->body(), $files);

        $paths = [];
        foreach ($files[1] as $file) {
            $this->client->request('GET', $file);
            self::assertResponseIsSuccessful($file);
            preg_match_all('#<loc>http://localhost([^<]*)</loc>#', $this->body(), $locations);
            $paths = [...$paths, ...array_map('html_entity_decode', $locations[1])];
        }

        return $paths;
    }
}
