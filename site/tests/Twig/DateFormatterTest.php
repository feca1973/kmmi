<?php

namespace App\Tests\Twig;

use App\Twig\DateFormatter;
use DateTime;
use PHPUnit\Framework\TestCase;

final class DateFormatterTest extends TestCase
{
    public function testADateIsWrittenInHungarian(): void
    {
        self::assertSame('2024. március 05.', (new DateFormatter())->normalizedDate(new DateTime('2024-03-05 18:30')));
        self::assertSame('2019. december 24.', (new DateFormatter())->normalizedDate(new DateTime('2019-12-24')));
    }

    public function testAnEventOfOneDayHasOneDate(): void
    {
        $formatter = new DateFormatter();

        self::assertSame(
            '2024. május 01.',
            $formatter->eventDate(new DateTime('2024-05-01 10:00'), new DateTime('2024-05-01 18:00'))
        );
        self::assertSame(
            '2024. május 01. - 2024. május 03.',
            $formatter->eventDate(new DateTime('2024-05-01'), new DateTime('2024-05-03'))
        );
    }
}
