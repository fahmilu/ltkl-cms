<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Where the image sits relative to the text in a Text Image block.
 *
 * Blocks saved before the option existed have no value, so anything
 * unrecognised reads as RIGHT — the layout those blocks already have.
 */
enum ImagePosition: string implements HasLabel
{
    case TOP = 'top';
    case LEFT = 'left';
    case BOTTOM = 'bottom';
    case RIGHT = 'right';

    public function getLabel(): string
    {
        return match ($this) {
            self::TOP => 'Top',
            self::LEFT => 'Left',
            self::BOTTOM => 'Bottom',
            self::RIGHT => 'Right',
        };
    }

    /**
     * The position of a block, defaulting to RIGHT.
     */
    public static function fromState($value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return is_string($value)
            ? (self::tryFrom($value) ?? self::RIGHT)
            : self::RIGHT;
    }
}
