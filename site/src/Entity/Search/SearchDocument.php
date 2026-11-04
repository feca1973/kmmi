<?php

namespace App\Entity\Search;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A page of the site as the full text search knows it: its title, its lead (as the result shows it) and its text. The
 * rows are written by SearchIndexer (SQL, the table is only mapped here for the schema) and are rebuilt from the
 * content by `app:search:reindex`.
 *
 * The collation is accent and case insensitive (a visitor finds "egyesület" with "egyesulet" too); the full text index
 * of the title alone is for the weight of the title, the one of the title and the text is for the search itself.
 *
 * @SuppressWarnings("PHPMD.UnusedPrivateField")
 */
#[ORM\Table(
    name: 'search_document',
    options: ['charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci'],
)]
#[ORM\UniqueConstraint(name: 'uniq_search_document_entity', columns: ['type', 'entity_id'])]
#[ORM\Index(name: 'ft_search_document_title', columns: ['title'], flags: ['fulltext'])]
#[ORM\Index(name: 'ft_search_document_title_body', columns: ['title', 'body_text'], flags: ['fulltext'])]
#[ORM\Entity]
class SearchDocument
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /** The kind of the content: article, event, monument, organization. */
    #[ORM\Column(type: Types::STRING, length: 32)]
    private string $type = '';

    #[ORM\Column(name: 'entity_id', type: Types::INTEGER)]
    private int $entityId = 0;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $title = '';

    /** The lead as the result list shows it (HTML; "lead" is a reserved word of MySQL 8). */
    #[ORM\Column(name: 'lead_html', type: Types::TEXT)]
    private string $leadHtml = '';

    /** The searched text, without tags: the longest texts of the site are a few megabytes. */
    #[ORM\Column(name: 'body_text', type: Types::TEXT, length: 16777215)]
    private string $bodyText = '';

    /** The public path of the page (a slug is up to 255 characters, the path has its folders too). */
    #[ORM\Column(type: Types::STRING, length: 1024)]
    private string $url = '';

    /** Unix timestamp: the page is not found before it. */
    #[ORM\Column(name: 'published_at', type: Types::INTEGER, nullable: true)]
    private ?int $publishedAt = null;

    /** Unix timestamp of the last indexing. */
    #[ORM\Column(name: 'updated_at', type: Types::INTEGER)]
    private int $updatedAt = 0;
}
