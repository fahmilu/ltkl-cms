<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SocialMedia: string implements HasLabel
{
    case FACEBOOK = 'fb';
    case X = 'x';
    case INSTAGRAM = 'ig';
    case LINKEDIN = 'li';
    case YOUTUBE = 'yt';

    public function getLabel(): string
    {
        return match ($this) {
            self::FACEBOOK => 'Facebook',
            self::X => 'X',
            self::INSTAGRAM => 'Instagram',
            self::LINKEDIN => 'Linkedin',
            self::YOUTUBE => 'Youtube',
        };
    }
}

