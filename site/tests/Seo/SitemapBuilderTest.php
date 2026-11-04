<?php

namespace App\Tests\Seo;

use App\Seo\PublicUrlBuilder;
use App\Seo\RobotsTxtBuilder;
use App\Seo\SitemapBuilder;
use App\Seo\SitemapEntry;
use App\Seo\SitemapProviderInterface;
use LogicException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class SitemapBuilderTest extends TestCase
{
    public function testTheHomePageComesFirstAndIsDatedByTheNewestContent(): void
    {
        $builder = $this->builder([
            $this->provider('news', [new SitemapEntry('/cikkek/a', 100), new SitemapEntry('/cikkek/b', 300)]),
            $this->provider('pages', [new SitemapEntry('/kepek', 50), new SitemapEntry('/')]),
        ]);

        $files = $builder->files();

        self::assertSame(['news', 'pages'], array_map(static fn ($file): string => $file->name, $files));
        self::assertSame('/', $files[1]->entries[0]->path);
        self::assertSame(300, $files[1]->entries[0]->lastModified);
        self::assertSame('/kepek', $files[1]->entries[1]->path);
    }

    public function testAPathAppearsOnce(): void
    {
        $builder = $this->builder([
            $this->provider('a', [new SitemapEntry('/x'), new SitemapEntry('/y')]),
            $this->provider('b', [new SitemapEntry('/x')]),
        ]);

        $paths = [];
        foreach ($builder->files() as $file) {
            foreach ($file->entries as $entry) {
                $paths[] = $entry->path;
            }
        }

        self::assertSame(['/x', '/y'], $paths);
    }

    public function testABigSectionContinuesInANewFile(): void
    {
        $entries = [];
        for ($index = 0; $index <= SitemapBuilder::MAX_URLS_PER_FILE; ++$index) {
            $entries[] = new SitemapEntry('/cikkek/' . $index);
        }

        $files = $this->builder([$this->provider('articles', $entries)])->files();

        self::assertSame(['articles', 'articles-2'], array_map(static fn ($file): string => $file->name, $files));
        self::assertCount(1, $files[1]->entries);
        self::assertSame('/sitemap-articles-2.xml', $files[1]->path());
    }

    public function testTheXmlIsAbsoluteAndEscaped(): void
    {
        $builder = $this->builder([$this->provider('pages', [new SitemapEntry('/a?b=1&c=2', 1700000000)])]);
        $files = $builder->files();

        $index = $builder->indexXml($files);
        $file = $builder->fileXml($files[0]);

        self::assertStringContainsString('<loc>https://kmmi.test/sitemap-pages.xml</loc>', $index);
        self::assertStringContainsString('<lastmod>2023-11-14T22:13:20+00:00</lastmod>', $index);
        self::assertStringContainsString('<loc>https://kmmi.test/a?b=1&amp;c=2</loc>', $file);
        self::assertSame(1700000000, $builder->lastModified($files));
    }

    public function testAnInvalidSectionNameIsRefused(): void
    {
        $this->expectException(LogicException::class);

        $this->builder([$this->provider('Not-Valid', [new SitemapEntry('/')])])->files();
    }

    public function testRobotsTxtLetsTheCrawlersInAndClosesThePrivateAreas(): void
    {
        $robots = (new RobotsTxtBuilder($this->urls(), true))->build();

        self::assertStringContainsString("User-agent: *\nAllow: /\n", $robots);
        self::assertStringContainsString("Disallow: /admin/\n", $robots);
        self::assertStringContainsString("Sitemap: https://kmmi.test/sitemap.xml\n", $robots);
    }

    public function testRobotsTxtClosesEverythingWhenIndexingIsOff(): void
    {
        self::assertSame("User-agent: *\nDisallow: /\n", (new RobotsTxtBuilder($this->urls(), false))->build());
    }

    public function testTheOriginIsTheRequestOneWithoutAConfiguredOne(): void
    {
        $stack = new RequestStack();
        $stack->push(Request::create('http://localhost:8081/cikkek'));

        self::assertSame('http://localhost:8081/a', (new PublicUrlBuilder($stack, 'not a url'))->absoluteUrl('/a'));
        self::assertSame('/a', (new PublicUrlBuilder(new RequestStack(), ''))->absoluteUrl('/a'));
    }

    /**
     * @param list<SitemapProviderInterface> $providers
     */
    private function builder(array $providers): SitemapBuilder
    {
        return new SitemapBuilder($providers, $this->urls());
    }

    private function urls(): PublicUrlBuilder
    {
        return new PublicUrlBuilder(new RequestStack(), 'https://kmmi.test/');
    }

    /**
     * @param list<SitemapEntry> $entries
     */
    private function provider(string $section, array $entries): SitemapProviderInterface
    {
        return new class ($section, $entries) implements SitemapProviderInterface {
            /**
             * @param list<SitemapEntry> $entries
             */
            public function __construct(private readonly string $section, private readonly array $entries)
            {
            }

            public function section(): string
            {
                return $this->section;
            }

            public function entries(): iterable
            {
                return $this->entries;
            }
        };
    }
}
