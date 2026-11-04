<?php

namespace App\Tests\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaValidator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class MappingTest extends KernelTestCase
{
    /** The tables that are not from the legacy system (see the migrations). */
    private const NEW_TABLES = ['search_document'];

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testMappingIsValid(): void
    {
        $errors = (new SchemaValidator($this->entityManager))->validateMapping();

        self::assertSame([], $errors);
    }

    public function testAllLegacyTablesAreMapped(): void
    {
        $tables = array_map(
            fn ($metadata) => $metadata->getTableName(),
            $this->entityManager->getMetadataFactory()->getAllMetadata()
        );
        sort($tables);

        // 37 legacy tables, minus `migration_versions` (not an entity) and the `events_organization` join table. The
        // tables of the new features (they come with migrations) are not legacy tables.
        self::assertCount(35, array_diff($tables, self::NEW_TABLES));
        self::assertContains('search_document', $tables);
        self::assertContains('articles', $tables);
        self::assertContains('articles_translation', $tables);
        self::assertContains('user', $tables);
        self::assertNotContains('migration_versions', $tables);
    }
}
