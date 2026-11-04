<?php

namespace App\Tests\Search;

use App\Search\SearchEngineInterface;
use App\Search\SearchHit;
use App\Tests\Support\PublicSiteFixtures;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

final class FulltextSearchEngineTest extends KernelTestCase
{
    use ResetDatabase;

    private SearchEngineInterface $engine;
    private EntityManagerInterface $entityManager;
    private Connection $connection;
    private PublicSiteFixtures $fixtures;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->engine = self::getContainer()->get(SearchEngineInterface::class);
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->connection = self::getContainer()->get(Connection::class);
        $this->fixtures = new PublicSiteFixtures($this->entityManager);
        $this->fixtures->create();
    }

    /**
     * @return iterable<string, array{string, list<string>}>
     */
    public static function terms(): iterable
    {
        yield 'a word of the title' => ['kossuth', ['/emlekhelyek/szobor/kossuth-szobor']];
        yield 'the other word of the title' => ['szobor', ['/emlekhelyek/szobor/kossuth-szobor']];
        yield 'a word of the text' => ['emlékmű', ['/emlekhelyek/szobor/kossuth-szobor']];
        yield 'without accents and in capitals' => ['EMLEKMU', ['/emlekhelyek/szobor/kossuth-szobor']];
        yield 'the beginning of a word' => ['rendezv', ['/rendezvenyek/unnep-unnepseg/magyar-kultura-napja']];
        yield 'the settlement of a monument' => ['tecso', ['/emlekhelyek/szobor/kossuth-szobor']];
        yield 'every word is needed' => ['kossuth tecső', ['/emlekhelyek/szobor/kossuth-szobor']];
        yield 'words of different pages' => ['kossuth beregszász', []];
        yield 'no such word' => ['nincsilyenszo', []];
        yield 'an empty term' => ['', []];
        yield 'only operators' => ['"*-+', []];
    }

    /**
     * @param list<string> $paths
     */
    #[DataProvider('terms')]
    public function testFindingTheWordsOfAPage(string $term, array $paths): void
    {
        $hits = $this->engine->load($this->engine->findIds($term));

        self::assertSame($paths, array_map(static fn (SearchHit $hit): string => $hit->url, $hits));
    }

    public function testTheTitleWeighsMoreThanTheText(): void
    {
        $this->fixtures->addNews('Másik', 'zebra zebra zebra zebra');
        $this->fixtures->addNews('Zebra', 'kicsi');
        $this->entityManager->flush();

        $titles = array_map(
            static fn (SearchHit $hit): string => $hit->title,
            $this->engine->load($this->engine->findIds('zebra'))
        );

        self::assertSame(['Zebra', 'Másik'], $titles);
    }

    public function testAContentIsNotFoundBeforeItsPublication(): void
    {
        $article = $this->fixtures->addNews('Jövőbeli hír', '', '+3 days');
        $this->entityManager->flush();

        self::assertSame([], $this->engine->findIds('jövőbeli'));

        $article->setPubDate(new DateTime('-1 hour'));
        $this->entityManager->flush();

        self::assertCount(1, $this->engine->findIds('jövőbeli'));
    }

    public function testTheHitsComeInTheOrderOfTheIds(): void
    {
        $ids = array_map('intval', $this->connection->fetchFirstColumn('SELECT id FROM search_document ORDER BY id'));
        $reversed = array_reverse($ids);

        $hits = $this->engine->load($reversed);

        self::assertCount(count($ids), $hits);
        $expected = $this->connection->fetchFirstColumn(
            'SELECT title FROM search_document ORDER BY FIELD(id, ' . implode(',', $reversed) . ')'
        );
        self::assertSame($expected, array_map(static fn (SearchHit $hit): string => $hit->title, $hits));
        self::assertSame([], $this->engine->load([]));
        self::assertSame([], $this->engine->load([999999]));
    }

    public function testTheLeadOfAHitIsTheStoredHtml(): void
    {
        $hit = $this->engine->load($this->engine->findIds('cikk szövege'))[0] ?? null;

        self::assertInstanceOf(SearchHit::class, $hit);
        self::assertSame('<p>Bevezető szöveg.</p>', $hit->lead);
    }
}
