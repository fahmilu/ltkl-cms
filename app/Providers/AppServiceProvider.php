<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Notifications\Livewire\Notifications;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Notifications::alignment(Alignment::Center);
        Notifications::verticalAlignment(VerticalAlignment::End);
        Filament::registerNavigationGroups([
            NavigationGroup::make('Contents')
                ->label('Contents'),
            NavigationGroup::make('Masters')
                ->label('Masters'),
            NavigationGroup::make('Administration')
                ->label('Administration'),
            NavigationGroup::make('Settings')
                ->label('Settings'),
        ]);
    }
}
