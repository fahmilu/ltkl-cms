<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * How a Collection block lays its records out.
 *
 * Only the Participation Pathways source offers a choice today; blocks saved
 * before it existed carry no value, so anything unrecognised reads as
 * SIDE_ACCORDION — the layout those blocks already have.
 */
enum CollectionDisplay: string implements HasLabel
{
    case SIDE_ACCORDION = 'side_accordion';
    case CARD = 'card';
    case FULL_ACCORDION = 'full_accordion';

    public function getLabel(): string
    {
        return match ($this) {
            self::SIDE_ACCORDION => 'Side Accordion',
            self::CARD => 'Card',
            self::FULL_ACCORDION => 'Full Accordion',
        };
    }

    /**
     * The display of a block, defaulting to SIDE_ACCORDION.
     */
    public static function fromState($value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return is_string($value)
            ? (self::tryFrom($value) ?? self::SIDE_ACCORDION)
            : self::SIDE_ACCORDION;
    }
}
