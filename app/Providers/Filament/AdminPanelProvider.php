<?php

namespace App\Providers\Filament;

use App\Filament\Resources\OrderResource\Widgets\ExpenseByMonth;
use App\Filament\Resources\OrderResource\Widgets\MonthlyExpenseIncome;
use App\Filament\Resources\OrderResource\Widgets\PayStatusCount;
use App\Filament\Resources\OrderResource\Widgets\ShippingStatusCount;
use App\Filament\Resources\OrderResource\Widgets\TotalSaleBasedOnMonth;
use App\Filament\Resources\OrderResource\Widgets\ShippingClassRatio;
use App\Filament\Widgets\Metrics;
use App\Http\Middleware\LocaleMiddleware;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use ShuvroRoy\FilamentSpatieLaravelHealth\FilamentSpatieLaravelHealthPlugin;
use Yebor974\Filament\RenewPassword\RenewPasswordPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->spa()
            ->id('admin')
            ->path('admin')
            ->brandLogo(asset('logo.light.png'))
            ->darkModeBrandLogo(asset('logo.dark.png'))
            ->brandLogoHeight('3rem')
            ->favicon(asset('favicon.ico'))
            ->login()
            ->emailVerification()
            ->profile(isSimple: false)
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Metrics::class,
                PayStatusCount::class,
                ShippingStatusCount::class,
                ShippingClassRatio::class,
                TotalSaleBasedOnMonth::class,
                MonthlyExpenseIncome::class,
                ExpenseByMonth::class,
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
                LocaleMiddleware::class
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                FilamentSpatieLaravelHealthPlugin::make(),
                FilamentApexChartsPlugin::make(),
                FilamentShieldPlugin::make(),
                RenewPasswordPlugin::make()
                ->timestampColumn('last_renew_password_at')
                ->passwordExpiresIn(days: 30)
            
            ])
            ->maxContentWidth(MaxWidth::Full)
            ->sidebarCollapsibleOnDesktop()
            ->unsavedChangesAlerts()
            ->databaseTransactions()
            ->databaseNotifications()
            ->databaseNotificationsPolling('10s');
    }
}
