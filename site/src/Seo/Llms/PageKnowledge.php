<?php

namespace App\Seo\Llms;

use App\Entity\Article\Article;
use App\Entity\Doc\Doc;
use App\Entity\Event\Events;
use App\Entity\Monuments\Monument;
use App\Entity\Organization\Organization;
use App\Entity\Region\Cities;
use App\Seo\PublicPaths;
use App\Service\PlainText;
use App\Twig\DateFormatter;

/**
 * The public pages as Markdown pages: what a visitor reads on them, without the frame.
 *
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
class PageKnowledge
{
    public function __construct(
        private readonly PlainText $text,
        private readonly DateFormatter $dates,
        private readonly PublicPaths $paths,
    ) {
    }

    public function article(Article $article): MarkdownPage
    {
        $type = $article->getArticleType();
        $published = $article->getPubDate();

        return new MarkdownPage(
            $this->text->from($article->getTitle()),
            $this->paths->article((int) $type?->getId(), (string) $type?->getSlug(), (string) $article->getSlug()),
            [
                'Rovat' => $this->text->from($type?->getName()),
                'Közzétéve' => $published === null ? '' : $this->dates->normalizedDate($published),
                'Szerző' => $this->text->from($article->getAuthorName()),
                'Forrás' => $this->text->from($article->getAuthorSource()),
            ],
            [
                ['heading' => null, 'html' => (string) $article->getLead()],
                ['heading' => null, 'html' => (string) $article->getContent()],
            ],
            ($article->getUpdateDate() ?? $published)?->getTimestamp(),
        );
    }

    public function document(Doc $doc): MarkdownPage
    {
        return new MarkdownPage(
            $this->text->from($doc->getDefaultTitle()),
            $this->paths->document((string) $doc->getSlug()),
            [],
            [
                ['heading' => null, 'html' => (string) $doc->getLead()],
                ['heading' => null, 'html' => (string) $doc->getText()],
            ],
            $doc->getUpdateDate()?->getTimestamp(),
        );
    }

    /**
     * @param bool $withContent without it only the catalog entry (the lead) is given: the text of a periodical issue is
     *                          megabytes long, it belongs to the Markdown page of the item, not to the llms-full.txt
     */
    public function libraryItem(Article $book, bool $withContent = true): MarkdownPage
    {
        $category = $book->getBookCategory();
        $parent = $category?->getParent();

        return new MarkdownPage(
            $this->text->from($book->getTitle()),
            $this->paths->libraryItem((string) $category?->getSlug(), (string) $book->getSlug()),
            [
                'Kategória' => implode(' / ', array_filter([$parent?->getName(), $category?->getName()])),
                'Szerző' => $this->text->from($book->getBookAuthor()),
                'A kiadás éve' => (string) $book->getBookReleaseYear(),
            ],
            [
                ['heading' => null, 'html' => (string) $book->getLead()],
                ['heading' => null, 'html' => $withContent ? (string) $book->getContent() : ''],
            ],
            ($book->getUpdateDate() ?? $book->getPubDate())?->getTimestamp(),
        );
    }

    public function actor(Article $actor): MarkdownPage
    {
        $type = $actor->getActorType();

        return new MarkdownPage(
            $this->text->from($actor->getTitle()),
            $this->paths->actor((string) $type?->getSlug(), (string) $actor->getSlug()),
            [
                'Típus' => $this->text->from($type?->getName()),
                'Kapcsolódó település' => $this->text->from($actor->getCity()?->getDefaultTitle()),
            ],
            [
                ['heading' => null, 'html' => (string) $actor->getLead()],
                ['heading' => null, 'html' => (string) $actor->getContent()],
            ],
            ($actor->getUpdateDate() ?? $actor->getPubDate())?->getTimestamp(),
        );
    }

    /**
     * An institution has no page of its own, it is on the list of its type.
     */
    public function education(Article $institution): MarkdownPage
    {
        $type = $institution->getEducationType();

        return new MarkdownPage(
            $this->text->from($institution->getTitle()),
            '/oktatasi-intezmenyek/' . $type?->getSlug(),
            ['Típus' => $this->text->from($type?->getName())],
            [
                ['heading' => null, 'html' => (string) $institution->getLead()],
                ['heading' => null, 'html' => (string) $institution->getContent()],
            ],
            ($institution->getUpdateDate() ?? $institution->getPubDate())?->getTimestamp(),
        );
    }

    public function event(Events $event): MarkdownPage
    {
        $from = $event->getDateFrom();
        $until = $event->getDateTo();
        $public = array_filter(
            $event->getOrganizations()->toArray(),
            static fn (Organization $organization): bool => (bool) $organization->isActive()
        );
        $organizations = array_map(
            fn (Organization $organization): string => $this->text->from($organization->getDefaultTitle()),
            $public
        );

        return new MarkdownPage(
            $this->text->from($event->getTitle()),
            $this->paths->event((string) $event->getType()?->getSlug(), (string) $event->getSlug()),
            [
                'Időpont' => $from !== null && $until !== null ? $this->dates->eventDate($from, $until) : '',
                'Helyszín' => $this->text->from($event->getPlaceName() ?: $event->getCity()?->getDefaultTitle()),
                'Település' => $this->text->from($event->getCity()?->getDefaultTitle()),
                'Jelleg' => $this->text->from($event->getType()?->getName()),
                'Szervezők' => implode(', ', $organizations),
                'Kapcsolattartó' => $this->text->from($event->getContactPerson()),
                'Telefon' => (string) $event->getTelephone(),
                'E-mail' => (string) $event->getEmail(),
            ],
            [
                ['heading' => null, 'html' => (string) $event->getLead()],
                ['heading' => null, 'html' => (string) $event->getContent()],
            ],
            $event->getUpdateDate()?->getTimestamp(),
        );
    }

    public function monument(Monument $monument): MarkdownPage
    {
        $created = $monument->getCreatedDate();

        return new MarkdownPage(
            $this->text->from($monument->getName()),
            $this->paths->monument((string) $monument->getType()?->getSlug(), (string) $monument->getSlug()),
            [
                'Típus' => (string) $monument->getType(),
                'Besorolás' => (string) $monument->getLocalization(),
                'Létező vagy eltűnt' => (string) $monument->getExisting(),
                'Település' => (string) $monument->getPlaceCity(),
                'Cím' => (string) $monument->getPlaceAddress(),
                'GPS' => $monument->getLat() === null ? '' : $monument->getLat() . ', ' . $monument->getLng(),
                'Készítés ideje' => $created === null ? '' : $this->dates->normalizedDate($created),
                'Készíttető' => (string) $monument->getSubmitter(),
                'Készítő' => (string) $monument->getCreatorName(),
                'Állapota' => (string) $monument->getMomumentCondition(),
                'Összeállította' => (string) $monument->getCompiledBy(),
            ],
            [
                ['heading' => null, 'html' => (string) $monument->getLongDescription()],
                ['heading' => 'Felirata', 'html' => (string) $monument->getMonumentCaption()],
                ['heading' => 'Forrásinformációk', 'html' => (string) $monument->getSourceInfo()],
            ],
            $monument->getUpdateDate()?->getTimestamp(),
        );
    }

    public function organization(Organization $organization): MarkdownPage
    {
        return new MarkdownPage(
            $this->text->from($organization->getDefaultTitle()),
            $this->paths->organization((string) $organization->getSlug()),
            [
                'Alapítás éve' => (string) $organization->getInstitutionYear(),
                'Cím' => (string) $organization->getPlaceAddress(),
                'Vezető' => (string) $organization->getOfficialPersonName(),
                'Telefon' => (string) $organization->getOfficialPersonTelephoneOffice(),
                'E-mail' => (string) $organization->getOfficialEmail(),
                'Kapcsolattartó' => (string) $organization->getContactPersonName(),
                'Honlap' => (string) $organization->getContactUrl(),
            ],
            [
                ['heading' => 'Működési területe', 'html' => (string) $organization->getFieldOfActivityId()],
                ['heading' => 'Szervezet célja', 'html' => (string) $organization->getGoal()],
                ['heading' => 'Szolgáltatások', 'html' => (string) $organization->getServices()],
                ['heading' => 'Bemutatás', 'html' => (string) $organization->getIntroduction()],
                ['heading' => 'Vezető bemutatása', 'html' => (string) $organization->getOfficialPersonShortCv()],
            ],
            $organization->getUpdateDate()?->getTimestamp(),
        );
    }

    public function city(Cities $city): MarkdownPage
    {
        $region = $city->getRegions();

        return new MarkdownPage(
            $this->text->from($city->getDefaultTitle()),
            $this->paths->city((string) $region?->getSlug(), (string) $city->getSlug()),
            ['Járás' => $this->text->from($region?->getDefaultTitle())],
            [
                ['heading' => null, 'html' => (string) $city->getLead()],
                ['heading' => null, 'html' => (string) $city->getContent()],
            ],
            $city->getUpdateDate()?->getTimestamp(),
        );
    }
}
