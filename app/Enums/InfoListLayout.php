<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * How an Info List block's items are laid out on the frontend.
 */
enum InfoListLayout: string implements HasLabel
{
    case ACCORDION = 'accordion';
    case CARD = 'card';

    public function getLabel(): string
    {
        return match ($this) {
            self::ACCORDION => 'Accordion',
            self::CARD => 'Card',
        };
    }
}
