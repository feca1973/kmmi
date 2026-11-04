<?php

namespace App\Tests\Twig;

use App\Service\PlainText;
use App\Twig\TextFilters;
use PHPUnit\Framework\TestCase;

final class TextFiltersTest extends TestCase
{
    public function testPlainTextHasNoTagsNoEntitiesAndSingleSpaces(): void
    {
        $filters = new TextFilters(new PlainText());

        self::assertSame('„Szia” – világ', $filters->plainText('&#8222;Szia&#8221; &#8211;  <b>világ</b>'));
        self::assertSame('Egy Kettő', $filters->plainText("<p>Egy</p>\n\n<p> Kettő </p>"));
        self::assertSame('', $filters->plainText(null));
    }

    public function testEscapedMarkupStaysText(): void
    {
        self::assertSame('<b>nem tag</b>', (new TextFilters(new PlainText()))->plainText('&lt;b&gt;nem tag&lt;/b&gt;'));
    }
}
