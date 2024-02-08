<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Navigation\UserMenuItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Filament::serving(function () {
        //     Filament::registerUserMenuItems([
        //         UserMenuItem::make()
        //             ->label('Settings')
        //             ->url(route('filament.pages.settings'))
        //             ->icon('heroicon-s-cog'),
        //         // ...
        //     ]);
        // });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();
    }
}
