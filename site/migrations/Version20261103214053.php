<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * The index of the full text search of the public site (see App\Search). Only this new table is created, the legacy
 * tables stay as they are. The table is filled by `php bin/console app:search:reindex`.
 */
final class Version20261103214053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Full text search index (search_document)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE search_document (
                id INT AUTO_INCREMENT NOT NULL,
                type VARCHAR(32) NOT NULL,
                entity_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                lead_html LONGTEXT NOT NULL,
                body_text MEDIUMTEXT NOT NULL,
                url VARCHAR(1024) NOT NULL,
                published_at INT DEFAULT NULL,
                updated_at INT NOT NULL,
                FULLTEXT INDEX ft_search_document_title (title),
                FULLTEXT INDEX ft_search_document_title_body (title, body_text),
                UNIQUE INDEX uniq_search_document_entity (type, entity_id),
                PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE search_document');
    }
}
