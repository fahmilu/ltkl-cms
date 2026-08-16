<?php

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Enums\ThemeMode;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        FilamentView::registerRenderHook(
            'panels::body.end',
            fn () => view('filament.hooks.custom-rich-editor-labels')
        );
        return $panel
            ->default()
            ->id('administrator')
            ->path('administrator')
            ->font('TASA Explorer', provider: GoogleFontProvider::class)
            ->viteTheme('resources/css/filament/administrator/theme.css')
            ->favicon(asset('favicon.ico'))
            ->brandLogo(asset('logo.svg'))
            ->brandName(config('app.name'))
            ->brandLogoHeight('40px')
            ->defaultThemeMode(ThemeMode::Light)
            ->login()
            ->topbar(true)
            ->profile(isSimple: true)
            ->topNavigation(false)
            ->sidebarCollapsibleOnDesktop(false)
            ->databaseNotifications()
            ->maxContentWidth(Width::SevenExtraLarge)
            ->spa()
            ->colors([
                'primary' => Color::Cyan,
                'info' => Color::Cyan,
                'gray' => Color::Gray,
                'success' => Color::Lime,
                'warning' => Color::Yellow,
                'danger' => Color::Rose,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                //Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make()->registerNavigation(false),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
