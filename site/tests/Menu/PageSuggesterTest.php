<?php

namespace App\Tests\Menu;

use App\Menu\PageSourceInterface;
use App\Menu\PageSuggester;
use App\Menu\PageSuggestion;
use App\Menu\TitleMatcher;
use PHPUnit\Framework\TestCase;

final class PageSuggesterTest extends TestCase
{
    public function testTheSourcesAreAskedInOrderAndTheFirstPagesAreKept(): void
    {
        $suggester = new PageSuggester(new TitleMatcher(), [
            $this->source('Lista', 3),
            $this->source('Cikk', 5),
            $this->source('Galéria', 6),
        ]);

        $titles = array_map(static fn (PageSuggestion $page): string => $page->title, $suggester->suggest('szo'));

        self::assertCount(PageSuggester::LIMIT, $titles);
        self::assertSame(['Lista 1', 'Lista 2', 'Lista 3', 'Cikk 1', 'Cikk 2'], array_slice($titles, 0, 5));
        self::assertSame('Galéria 4', $titles[PageSuggester::LIMIT - 1]);
    }

    public function testAShortTermAsksNobody(): void
    {
        $source = new class implements PageSourceInterface {
            public bool $asked = false;

            public function find(string $term): array
            {
                $this->asked = true;

                return [];
            }
        };
        $suggester = new PageSuggester(new TitleMatcher(), [$source]);

        self::assertSame([], $suggester->suggest('a'));
        self::assertSame([], $suggester->suggest('  Á '));
        self::assertFalse($source->asked);
    }

    private function source(string $kind, int $count): PageSourceInterface
    {
        return new class ($kind, $count) implements PageSourceInterface {
            public function __construct(private readonly string $kind, private readonly int $count)
            {
            }

            public function find(string $term): array
            {
                return array_map(
                    fn (int $number): PageSuggestion => new PageSuggestion(
                        $this->kind . ' ' . $number,
                        '/' . $number,
                        $this->kind
                    ),
                    range(1, $this->count)
                );
            }
        };
    }
}
