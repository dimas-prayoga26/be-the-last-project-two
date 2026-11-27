<?php

namespace App\Providers\Filament;

use App\Models\StudioProfile;
use Filament\Auth\Pages\Login;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    /**
     * Neutral greys that end in near-black, so buttons, links and toggles are black like the brand.
     *
     * @var array<int, string>
     */
    private const BRAND_BLACK = [
        50 => 'oklch(0.985 0 0)',
        100 => 'oklch(0.97 0 0)',
        200 => 'oklch(0.922 0 0)',
        300 => 'oklch(0.87 0 0)',
        400 => 'oklch(0.439 0 0)',
        500 => 'oklch(0.269 0 0)',
        600 => 'oklch(0.145 0 0)',
        700 => 'oklch(0.12 0 0)',
        800 => 'oklch(0.1 0 0)',
        900 => 'oklch(0.08 0 0)',
        950 => 'oklch(0.05 0 0)',
    ];

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('The Latest Project')
            ->brandLogo(asset('brand/tlp-logo-primary-black.svg'))
            ->darkModeBrandLogo(asset('brand/tlp-logo-primary-white.svg'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('brand/tlp-favicon.svg'))
            ->colors([
                'primary' => self::BRAND_BLACK,
                'gray' => Color::Neutral,
            ])
            ->font('Creato Display', provider: LocalFontProvider::class)
            ->darkMode(false)
            ->topNavigation()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->renderHook(
                PanelsRenderHook::SIMPLE_LAYOUT_START,
                fn (): View => view('filament.auth.login-visual', [
                    'imageUrl' => StudioProfile::query()->oldest('id')->first()?->getFirstMediaUrl('about_image', 'large') ?: null,
                ]),
                scopes: Login::class,
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->navigationGroups(['Portfolio', 'Studio', 'Settings'])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
