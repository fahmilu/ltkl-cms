<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PageTemplate: string implements HasLabel
{
    case HOME = 'home';
    case FLEXY = 'flexy';

    public function getLabel(): string
    {
        return match ($this) {
            self::HOME => 'Home',
            self::FLEXY => 'Flexy',
        };
    }
}

