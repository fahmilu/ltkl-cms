<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Where a block's button points: a link, or a file uploaded with the block
 * (a report, for instance).
 *
 * Blocks saved before the option existed have no value, so anything
 * unrecognised reads as URL — the only kind of button they could have.
 */
enum ButtonType: string implements HasLabel
{
    case URL = 'url';
    case FILE = 'file';

    public function getLabel(): string
    {
        return match ($this) {
            self::URL => 'URL',
            self::FILE => 'Upload file',
        };
    }

    /**
     * The button type of a block, defaulting to URL.
     */
    public static function fromState($value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return is_string($value)
            ? (self::tryFrom($value) ?? self::URL)
            : self::URL;
    }
}
