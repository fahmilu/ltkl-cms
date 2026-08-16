<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    /**
     * @return string|BackedEnum|Htmlable|null
     */
    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return Heroicon::OutlinedHome;
    }

    /**
     * @return int|array|int[]
     */
    public function getColumns(): int|array
    {
        return 1;
    }
}
