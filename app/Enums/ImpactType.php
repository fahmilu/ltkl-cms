<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * What one impact row on a kabupaten holds.
 *
 * Rows saved before the types existed carry no value at all, so anything
 * unrecognised reads as DATA — the shape those rows already have.
 */
enum ImpactType: string implements HasLabel
{
    case DATA = 'data';
    case QUOTE = 'quote';
    case TEXT = 'text';
    case IMAGE_TEXT = 'image_text';

    public function getLabel(): string
    {
        return match ($this) {
            self::DATA => 'Data',
            self::QUOTE => 'Quote',
            self::TEXT => 'Text',
            self::IMAGE_TEXT => 'Image Text',
        };
    }

    /**
     * The type of a row, defaulting to DATA.
     *
     * The form hands back an instance of this enum, since the select is built
     * from the class, while the database holds the plain string.
     */
    public static function fromState($value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return is_string($value)
            ? (self::tryFrom($value) ?? self::DATA)
            : self::DATA;
    }
}
