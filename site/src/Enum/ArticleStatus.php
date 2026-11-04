<?php

namespace App\Enum;

enum ArticleStatus: string
{
    case InEdit = 'edit';
    case WaitForReview = 'review';
    case Published = 'published';
    case Discarded = 'discarded';

    public function label(): string
    {
        return match ($this) {
            self::InEdit => 'Szerkesztés alatt',
            self::WaitForReview => 'Átnézésre vár',
            self::Published => 'Publikálva',
            self::Discarded => 'Elvetve',
        };
    }
}
