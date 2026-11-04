<?php

namespace App\Tests\Search;

use App\Search\BooleanQueryBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BooleanQueryBuilderTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string|null}>
     */
    public static function terms(): iterable
    {
        yield 'one word is a prefix' => ['szobor', '+szobor*'];
        yield 'every word is needed' => ['Kárpátalja szobor', '+kárpátalja* +szobor*'];
        yield 'the hyphen separates the words' => ['Kossuth-szobor', '+kossuth* +szobor*'];
        yield 'numbers are words' => ['1848 március', '+1848* +március*'];
        yield 'the same word once' => ['szobor SZOBOR', '+szobor*'];
        yield 'the operators of the boolean mode are lost' => [
            '+a "bb" -ccc* (dddd) @eeee ~ffff <gggg >hhhh',
            '+ccc* +dddd* +eeee* +ffff* +gggg* +hhhh*',
        ];
        yield 'a short word is left out' => ['a szobor', '+szobor*'];
        yield 'two letters are enough when there is nothing else' => ['az', '+az*'];
        yield 'one letter is not a word to search' => ['a', null];
        yield 'an empty term' => ['', null];
        yield 'only spaces' => ['   ', null];
        yield 'only operators' => ['"*-+', null];
        yield 'the number of the words is limited' => [
            'aaa bbb ccc ddd eee fff ggg hhh iii jjj kkk lll',
            '+aaa* +bbb* +ccc* +ddd* +eee* +fff* +ggg* +hhh* +iii* +jjj*',
        ];
    }

    #[DataProvider('terms')]
    public function testTheWordsOfTheTermMakeTheQuery(string $term, ?string $expected): void
    {
        self::assertSame($expected, (new BooleanQueryBuilder())->build($term));
    }
}
