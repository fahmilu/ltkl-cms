<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ExternalType: string implements HasLabel
{
    case LINK = '_link';
    case FILE = '_file';

    public function getLabel(): string
    {
        return match ($this) {
            self::LINK => 'Link',
            self::FILE => 'File',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::LINK => 'heroicon-o-link',
            self::FILE => 'heroicon-o-document-duplicate',
        };
    }

    public function getColor(): string|array|null
    {

        return match ($this) {
            self::LINK => 'success',
            self::FILE => 'warning',
        };
    }
}

