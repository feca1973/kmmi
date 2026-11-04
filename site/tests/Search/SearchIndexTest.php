<?php

namespace App\Tests\Search;

use App\Entity\Article\Article;
use App\Entity\Article\ArticleType;
use App\Entity\Monuments\Monument;
use App\Entity\Organization\Organization;
use App\Repository\Article\ArticleRepository;
use App\Tests\Support\PublicSiteFixtures;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * The search index follows the content: saving a content writes its row, a content that is not public has none.
 */
final class SearchIndexTest extends KernelTestCase
{
    use ResetDatabase;

    private EntityManagerInterface $entityManager;
    private Connection $connection;

    /**
     * @var array<string, string>
     */
    private array $urls;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->connection = self::getContainer()->get(Connection::class);
        $this->urls = (new PublicSiteFixtures($this->entityManager))->create();
    }

    public function testSavingThePublicContentsIndexesThem(): void
    {
        $types = $this->connection->fetchAllKeyValue('SELECT url, type FROM search_document');

        // The data pages of the fixtures have no article type, so they have no page under /cikkek and are not here.
        self::assertEqualsCanonicalizing([
            $this->urls['article'] => 'article',
            $this->urls['folk art article'] => 'article',
            '/cikkek/egyhazak/egyhazak-8211-cikk' => 'article',
            $this->urls['event'] => 'event',
            $this->urls['monument'] => 'monument',
            $this->urls['organization'] => 'organization',
        ], $types);
    }

    public function testARowHasTheTitleTheLeadAndTheTextOfThePage(): void
    {
        $article = $this->row($this->urls['article']);
        self::assertSame('Hírek – cikk', $article['title']);
        self::assertSame('<p>Bevezető szöveg.</p>', $article['lead_html']);
        self::assertSame('Bevezető szöveg. A cikk szövege.', $article['body_text']);
        self::assertEqualsWithDelta(strtotime('-2 days'), (int) $article['published_at'], 5);

        // The facts that are only on the page (the place, the settlement) are searched too.
        $event = $this->row($this->urls['event']);
        self::assertSame('Beregszász Beregszász Rendezvény. A rendezvény leírása.', $event['body_text']);
        self::assertNull($event['published_at']);

        // A monument has no lead: the page begins with its description.
        $monument = $this->row($this->urls['monument']);
        self::assertSame('<p>Az emlékmű leírása.</p>', $monument['lead_html']);
        self::assertSame('Tecső Szobor Az emlékmű leírása.', $monument['body_text']);
    }

    public function testAContentThatIsNotPublicIsNotOnTheSearch(): void
    {
        $monument = $this->entityManager->getRepository(Monument::class)->findOneBy([]);
        $monument?->setActive(false);
        $this->entityManager->flush();

        self::assertSame(5, $this->rowCount());
        self::assertSame([], $this->connection->fetchAllAssociative(
            'SELECT * FROM search_document WHERE type = ?',
            ['monument']
        ));
    }

    public function testAContentWithoutATextIsNotOnTheSearch(): void
    {
        $article = (new Article())
            ->setArticleType($this->entityManager->find(ArticleType::class, 6))
            ->setPubDate(new DateTime('-1 day'))
            ->setActive(true);
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        self::assertSame(6, $this->rowCount());
    }

    public function testSavingAContentUpdatesItsRow(): void
    {
        $id = $this->row($this->urls['article'])['id'];

        $this->newsArticle()->getTranslation()->setTitle('Új cím');
        $this->entityManager->flush();

        self::assertSame(6, $this->rowCount());
        self::assertContains('Új cím', $this->connection->fetchFirstColumn('SELECT title FROM search_document'));
        self::assertSame(
            $id,
            $this->connection->fetchOne('SELECT id FROM search_document WHERE title = ?', ['Új cím']),
            'The row is updated, not added.'
        );
    }

    public function testRemovingAContentRemovesItsRow(): void
    {
        $organization = $this->entityManager->getRepository(Organization::class)->findOneBy([]);
        self::assertNotNull($organization);
        $this->entityManager->remove($organization);
        $this->entityManager->flush();

        self::assertSame(5, $this->rowCount());
        self::assertFalse(
            $this->connection->fetchOne('SELECT 1 FROM search_document WHERE type = ?', ['organization'])
        );
    }

    public function testAnErrorOfTheIndexDoesNotBreakSavingTheContent(): void
    {
        $this->connection->executeStatement('DROP TABLE search_document');
        $monument = $this->entityManager->getRepository(Monument::class)->findOneBy([]);
        $monument?->setName('Más név');

        $this->entityManager->flush();
        $this->entityManager->clear();

        self::assertSame('Más név', $this->entityManager->getRepository(Monument::class)->findOneBy([])?->getName());
    }

    public function testTheReindexRebuildsTheWholeIndex(): void
    {
        $this->connection->executeStatement('DELETE FROM search_document');
        $this->connection->executeStatement(
            'INSERT INTO search_document (type, entity_id, title, lead_html, body_text, url, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)',
            ['article', 999999, 'Régi', '', 'régi', '/cikkek/hirek/regi', 1]
        );

        $tester = new CommandTester((new Application(self::$kernel))->find('app:search:reindex'));
        $status = $tester->execute([]);

        self::assertSame(0, $status, $tester->getDisplay());
        self::assertStringContainsString('6 pages are on the search.', $tester->getDisplay());
        self::assertSame(6, $this->rowCount());
        self::assertFalse(
            $this->connection->fetchOne('SELECT 1 FROM search_document WHERE title = ?', ['Régi']),
            'The row of a content that is gone is removed.'
        );
    }

    private function newsArticle(): Article
    {
        /** @var ArticleRepository $articles */
        $articles = $this->entityManager->getRepository(Article::class);
        $article = $articles->findActiveBySlug('hirek-8211-cikk');
        self::assertNotNull($article);

        return $article;
    }

    private function rowCount(): int
    {
        return (int) $this->connection->fetchOne('SELECT COUNT(*) FROM search_document');
    }

    /**
     * @return array<string, mixed>
     */
    private function row(string $url): array
    {
        $row = $this->connection->fetchAssociative('SELECT * FROM search_document WHERE url = ?', [$url]);
        self::assertIsArray($row, 'The page ' . $url . ' is on the search.');

        return $row;
    }
}
