<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * The brand colours a Text Image block may be filled with.
 *
 * The block only stores the hex value, so the frontend can paint the section
 * without knowing the palette. Blocks saved before the colour existed, and
 * blocks that are not rendered as a filled block, carry no value at all.
 */
enum BlockBackgroundColor: string implements HasLabel
{
    case CYAN = '#00A9C1';
    case MAGENTA = '#A20056';
    case TEAL = '#007A8A';
    case NAVY = '#2E3171';

    public function getLabel(): string
    {
        return match ($this) {
            self::CYAN => 'Cyan (#00A9C1)',
            self::MAGENTA => 'Magenta (#A20056)',
            self::TEAL => 'Teal (#007A8A)',
            self::NAVY => 'Navy (#2E3171)',
        };
    }

    /**
     * The stored value of a colour, or null when it is unset or unrecognised.
     */
    public static function fromState($value): ?string
    {
        if ($value instanceof self) {
            return $value->value;
        }

        return is_string($value) ? self::tryFrom($value)?->value : null;
    }
}
