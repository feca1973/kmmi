<?php

namespace App\Enum;

/**
 * Value types of the "unique" (site wide editable) items; the admin picks the form field by this type.
 */
enum UniqueType: string
{
    case String = 'string';
    case Text = 'text';
    case Elfinder = 'elfinder';
    case Number = 'number';
    case Money = 'money';
    case Percent = 'percent';
    case Ckeditor = 'ckeditor';

    public function label(): string
    {
        return match ($this) {
            self::String => 'Rövid szöveg',
            self::Text => 'Hosszú szöveg',
            self::Elfinder => 'Kép',
            self::Number => 'Szám',
            self::Money => 'Pénz',
            self::Percent => 'Százalék',
            self::Ckeditor => 'Formázható szöveg',
        };
    }
}
