<?php

namespace App\Tests\Search;

use App\Search\SearchTerm;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class SearchTermTest extends TestCase
{
    /**
     * @return iterable<string, array{array<string, mixed>, string|null}>
     */
    public static function queries(): iterable
    {
        yield 'the term of the form' => [['search_form' => ['term' => ' szobor ']], 'szobor'];
        yield 'no parameter' => [[], null];
        yield 'an empty term' => [['search_form' => ['term' => '']], null];
        yield 'only spaces' => [['search_form' => ['term' => '  ']], null];
        yield 'the form without the term' => [['search_form' => ['other' => 'x']], null];
        yield 'a term that is a list' => [['search_form' => ['term' => ['a', 'b']]], null];
        yield 'a form that is not a list' => [['search_form' => 'szobor'], null];
        yield 'the bare term is not the form' => [['term' => 'szobor'], null];
    }

    /**
     * @param array<string, mixed> $query
     */
    #[DataProvider('queries')]
    public function testTheTermIsTheOneOfTheForm(array $query, ?string $expected): void
    {
        $request = Request::create('/search/frontend', 'GET', $query);

        self::assertSame($expected, (new SearchTerm())->fromRequest($request));
    }
}
