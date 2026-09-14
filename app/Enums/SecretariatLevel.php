<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * The rank of a secretariat member in the organisation chart.
 *
 * Stored as a plain integer (1, 2 or 3) rather than the label text, so the
 * ranking survives a label rename.
 */
enum SecretariatLevel: int implements HasLabel
{
    case TOP_LEVEL = 1;
    case MANAGER = 2;
    case STAFF = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::TOP_LEVEL => 'Top Level',
            self::MANAGER => 'Manager',
            self::STAFF => 'Staff',
        };
    }
}
