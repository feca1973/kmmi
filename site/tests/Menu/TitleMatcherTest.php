<?php

namespace App\Tests\Menu;

use App\Menu\TitleMatcher;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TitleMatcherTest extends TestCase
{
    public function testTheNeedleIsLowerCaseWithoutAccents(): void
    {
        self::assertSame('rolunk', (new TitleMatcher())->needle('  Rólunk '));
        self::assertSame('uveg szobor', (new TitleMatcher())->needle('ÜVEG Szobor'));
        self::assertSame('oo uu', (new TitleMatcher())->needle('őÖ űŰ'));
    }

    /**
     * @return iterable<string, array{string, string, bool}>
     */
    public static function titles(): iterable
    {
        yield 'the same word' => ['Rólunk', 'rolunk', true];
        yield 'the beginning of a word' => ['Rendezvények', 'rendez', true];
        yield 'the inside of a title' => ['Kárpátaljai Magyar Művelődési Intézet', 'muvelodesi', true];
        yield 'with accents' => ['Népművészet', 'népművész', true];
        yield 'not in the title' => ['Rólunk', 'partner', false];
    }

    #[DataProvider('titles')]
    public function testATitleMatchesWithoutRegardToCaseAndAccents(string $title, string $term, bool $expected): void
    {
        $matcher = new TitleMatcher();

        self::assertSame($expected, $matcher->matches($title, $matcher->needle($term)));
    }
}
