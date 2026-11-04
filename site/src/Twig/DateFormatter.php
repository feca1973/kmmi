<?php

namespace App\Twig;

use DateTimeInterface;
use Twig\Attribute\AsTwigFunction;

/**
 * Hungarian date formats of the public pages: "2024. március 05.".
 */
class DateFormatter
{
    private const MONTHS = [
        '01' => 'január',
        '02' => 'február',
        '03' => 'március',
        '04' => 'április',
        '05' => 'május',
        '06' => 'június',
        '07' => 'július',
        '08' => 'augusztus',
        '09' => 'szeptember',
        '10' => 'október',
        '11' => 'november',
        '12' => 'december',
    ];

    #[AsTwigFunction('normalizedDate')]
    public function normalizedDate(DateTimeInterface $date): string
    {
        return sprintf('%s. %s %s.', $date->format('Y'), self::MONTHS[$date->format('m')], $date->format('d'));
    }

    #[AsTwigFunction('eventDate')]
    public function eventDate(DateTimeInterface $from, DateTimeInterface $until): string
    {
        $start = $this->normalizedDate($from);
        $end = $this->normalizedDate($until);

        return $start === $end ? $start : $start . ' - ' . $end;
    }
}
