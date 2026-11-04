<?php

namespace App\Enum;

enum MediaType: string
{
    case Audio = 'audio';
    case Video = 'video';

    /**
     * @return array<string, self> labels as keys, for the admin choice fields
     */
    public static function choices(): array
    {
        return array_combine(array_map(static fn (self $case) => $case->label(), self::cases()), self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::Audio => 'Audio',
            self::Video => 'Video',
        };
    }
}
