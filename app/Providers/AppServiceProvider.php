<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Illuminate\Support\ServiceProvider;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\DatabaseConnectionCountCheck;
use Spatie\Health\Checks\Checks\DatabaseTableSizeCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Checks\Checks\QueueCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Facades\Health;

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
        Filament::serving(function () {
            Filament::registerNavigationGroups([
                NavigationGroup::make()
                    ->label('Shop'),
                NavigationGroup::make()
                    ->label('Accounts'),
                NavigationGroup::make()
                    ->label('Configuration')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Settings')
                    ->collapsed(),
            ]);
        });

        //
        Health::checks([
            OptimizedAppCheck::new(),
            DebugModeCheck::new(),
            EnvironmentCheck::new(),
            DatabaseCheck::new(),
            DatabaseConnectionCountCheck::new(),
            DatabaseTableSizeCheck::new(),
            UsedDiskSpaceCheck::new(),
            QueueCheck::new()
        ]);
    }
}
