<?php

namespace App\Search;

use App\Entity\Article\Article;
use App\Entity\Article\ArticleTranslation;
use App\Entity\Event\Events;
use App\Entity\Event\EventsTranslation;
use App\Entity\Monuments\Monument;
use App\Entity\Organization\Organization;
use App\Entity\Organization\OrganizationTranslation;
use App\Seo\Llms\MarkdownPage;
use App\Seo\Llms\PageKnowledge;
use App\Service\PlainText;

/**
 * The search index entry of a content: the articles, events, monuments and organizations that have a public page (the
 * same as the legacy search found). The title and the path of the page come from PageKnowledge, so they are the ones of
 * the sitemap and of the Markdown pages; the searched text is what the page says (its text sections) and the words of
 * the page that are only in its facts (the place of an event, the settlement of a monument).
 *
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
class SearchEntryFactory
{
    public const ARTICLE = 'article';
    public const EVENT = 'event';
    public const MONUMENT = 'monument';
    public const ORGANIZATION = 'organization';

    /** @var list<class-string> */
    private const CONTENTS = [Article::class, Events::class, Monument::class, Organization::class];

    /** The length of the lead made of the text of a page that has no lead. */
    private const EXCERPT_LENGTH = 300;

    /** The column of the text is a MEDIUMTEXT (16 777 215 bytes). */
    private const MAX_BODY_BYTES = 16_000_000;

    public function __construct(
        private readonly PageKnowledge $pages,
        private readonly PlainText $text,
    ) {
    }

    /**
     * The content a changed entity belongs to: the article of an article translation and so on; the content itself;
     * null for everything that is not on the search.
     */
    public function owner(object $entity): ?object
    {
        $owner = match (true) {
            $entity instanceof ArticleTranslation,
            $entity instanceof EventsTranslation,
            $entity instanceof OrganizationTranslation => $entity->getTranslatable(),
            default => $entity,
        };

        // A new content has no id yet, so only its class tells whether it is on the search.
        foreach (self::CONTENTS as $class) {
            if ($owner instanceof $class) {
                return $owner;
            }
        }

        return null;
    }

    /**
     * The type and the id of the row of a content.
     *
     * @return array{string, int}|null
     */
    public function key(object $owner): ?array
    {
        return match (true) {
            $owner instanceof Article => [self::ARTICLE, (int) $owner->getId()],
            $owner instanceof Events => [self::EVENT, (int) $owner->getId()],
            $owner instanceof Monument => [self::MONUMENT, (int) $owner->getId()],
            $owner instanceof Organization => [self::ORGANIZATION, (int) $owner->getId()],
            default => null,
        };
    }

    /**
     * Null when the content has no public page, so it is not on the search.
     */
    public function create(object $owner): ?SearchEntry
    {
        return match (true) {
            $owner instanceof Article => $this->article($owner),
            $owner instanceof Events => $this->event($owner),
            $owner instanceof Monument => $this->monument($owner),
            $owner instanceof Organization => $this->organization($owner),
            default => null,
        };
    }

    /**
     * Only the published articles of an article type have a page (/cikkek/{type}/{slug}, /nepmuveszet/{slug}).
     */
    private function article(Article $article): ?SearchEntry
    {
        if ($article->isActive() !== true || $article->getArticleType() === null || $article->translate() === null) {
            return null;
        }

        return $this->entry(
            self::ARTICLE,
            (int) $article->getId(),
            $this->pages->article($article),
            (string) $article->getLead(),
            [],
            $article->getPubDate()?->getTimestamp()
        );
    }

    private function event(Events $event): ?SearchEntry
    {
        if ($event->isActive() !== true || $event->getType() === null || $event->translate() === null) {
            return null;
        }

        return $this->entry(
            self::EVENT,
            (int) $event->getId(),
            $this->pages->event($event),
            (string) $event->getLead(),
            [$event->getPlaceName(), $event->getCity()?->getDefaultTitle()]
        );
    }

    private function monument(Monument $monument): ?SearchEntry
    {
        if ($monument->isActive() !== true || $monument->getType() === null) {
            return null;
        }

        return $this->entry(
            self::MONUMENT,
            (int) $monument->getId(),
            $this->pages->monument($monument),
            '',
            [$monument->getPlaceCity(), (string) $monument->getType()]
        );
    }

    private function organization(Organization $organization): ?SearchEntry
    {
        if ($organization->isActive() !== true || $organization->translate() === null) {
            return null;
        }

        return $this->entry(
            self::ORGANIZATION,
            (int) $organization->getId(),
            $this->pages->organization($organization),
            '',
            []
        );
    }

    /**
     * @param list<string|null> $extras the words of the page that are not in its text sections
     */
    private function entry(
        string $type,
        int $id,
        MarkdownPage $page,
        string $lead,
        array $extras,
        ?int $publishedAt = null,
    ): SearchEntry {
        $text = $this->plainParts(array_column($page->sections, 'html'));
        $body = mb_strcut(implode(' ', [...$this->plainParts($extras), ...$text]), 0, self::MAX_BODY_BYTES);

        return new SearchEntry(
            $type,
            $id,
            $page->title,
            $this->lead($lead, implode(' ', $text)),
            $body,
            $page->path,
            $publishedAt
        );
    }

    /**
     * The visible texts of the HTML values, without the empty ones.
     *
     * @param list<string|null> $values
     *
     * @return list<string>
     */
    private function plainParts(array $values): array
    {
        return array_values(array_filter(
            array_map(fn (?string $html): string => $this->text->from($html), $values),
            static fn (string $part): bool => $part !== ''
        ));
    }

    /**
     * The lead as it was edited; the beginning of the text of the page, when the page has no lead.
     */
    private function lead(string $lead, string $text): string
    {
        if ($this->text->from($lead) !== '') {
            return $lead;
        }
        if ($text === '') {
            return '';
        }

        return '<p>' . htmlspecialchars(mb_strimwidth($text, 0, self::EXCERPT_LENGTH, '…'), ENT_QUOTES) . '</p>';
    }
}
