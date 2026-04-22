<?php

namespace App\Providers\Filament;

use App\Filament\Cliente\Navigation\UxPrototypeParentNavigationItems;
use App\Filament\Cliente\Pages\Dashboard;
use App\Http\Middleware\SetFilamentLocale;
use App\Models\Empresa;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Resma\FilamentAwinTheme\FilamentAwinTheme;

/**
 * BL: Login en /cliente/login; credenciales demo: cliente@tecben.com / password (ver ClienteEjemploSeeder).
 */
class ClientePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('cliente')
            ->path('cliente')
            ->login()
            ->colors([
                'primary' => '#3148c8',
            ])
            ->spa(false)
            ->favicon(asset('img/favicon_paco.png'))
            ->brandName('Paco')
            ->darkMode(false)
            ->font('Instrument Sans', provider: GoogleFontProvider::class)
            ->sidebarCollapsibleOnDesktop()
            ->brandLogo(asset('img/logo_paco.png'))
            ->brandLogoHeight('3rem')
            ->discoverResources(in: app_path('Filament/Cliente/Resources'), for: 'App\Filament\Cliente\Resources')
            ->resources([])
            ->discoverPages(in: app_path('Filament/Cliente/Pages'), for: 'App\Filament\Cliente\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Cliente/Widgets'), for: 'App\Filament\Cliente\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                SetFilamentLocale::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                FilamentAwinTheme::make(),
            ])
            ->viteTheme([
                'vendor/resma/filament-awin-theme/resources/css/theme.css',
                'resources/css/filament-sidebar-overrides.css',
            ])
            ->navigationGroups([
                'Storybook' => NavigationGroup::make('Storybook')
                    ->icon('heroicon-o-book-open')
                    ->collapsed(),
                'UX prototype' => NavigationGroup::make('UX prototype')
                    ->icon('heroicon-o-cube-transparent')
                    ->collapsed(),
            ])
            ->navigationItems(UxPrototypeParentNavigationItems::definitions())
            ->tenant(Empresa::class);
    }
}
