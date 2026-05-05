<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
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
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('web')
            ->colors([
                'primary' => Color::Blue,
                'danger' => Color::Red,
                'gray' => Color::Gray,
            ])
            ->brandName('flightSwap ')
            ->brandLogoHeight('2.5rem')
            ->userMenuItems([])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                // Removed: Widgets\FilamentInfoWidget::class,
            ])
            // Render hooks temporarily disabled to debug timeout issue
            // ->renderHook(
            //     'panels::topbar.end',
            //     fn () => view('filament.topbar.notification-icon'),
            // )
            // ->renderHook(
            //     'panels::topbar.end',
            //     fn () => view('filament.topbar.message-icon'),
            // )
            // ->renderHook(
            //     'panels::topbar.end',
            //     fn () => view('filament.topbar.language-switcher'),
            // )
            // ->renderHook(
            //     'panels::head.start',
            //     fn () => view('filament.hooks.rtl-support'),
            // )
            // ->renderHook(
            //     'panels::head.end',
            //     fn () => view('filament.hooks.admin-theme'),
            // )
            // ->renderHook(
            //     'panels::topbar.start',
            //     fn () => view('filament.topbar.brand-name'),
            // )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                \App\Http\Middleware\SetLanguage::class,
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
