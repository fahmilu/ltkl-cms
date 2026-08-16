<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PostType: string implements HasColor, HasIcon, HasLabel
{
    case ARTICLE = 'article';
    case VIDEO = 'video';
    case EVENT = 'event';
    case LIBRARY = 'library';
    case MEDIA_COVERAGE = 'media_coverage';

    public function getLabel(): string
    {
        return match ($this) {
            self::ARTICLE => 'Article',
            self::VIDEO => 'Video',
            self::EVENT => 'Event',
            self::LIBRARY => 'Library',
            self::MEDIA_COVERAGE => 'Media Coverage',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::ARTICLE => 'heroicon-o-document-text',
            self::VIDEO => 'heroicon-o-play-circle',
            self::EVENT => 'heroicon-o-calendar-days',
            self::LIBRARY => 'heroicon-o-book-open',
            self::MEDIA_COVERAGE => 'heroicon-o-megaphone',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::ARTICLE => 'primary',
            self::VIDEO => 'danger',
            self::EVENT => 'warning',
            self::LIBRARY => 'success',
            self::MEDIA_COVERAGE => 'info',
        };
    }
}
