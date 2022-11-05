<?php

namespace App\Providers;

use App\Helpers\RoleHelper;
use App\Charts\ItemSalesChart;
use App\Helpers\CategoryHelper;
use App\Helpers\RotationHelper;
use App\Helpers\SettingsHelper;
use App\Charts\ActivitySalesChart;
use App\Charts\IncomeHistoryChart;
use App\Charts\PurchaseHistoryChart;
use ConsoleTVs\Charts\Registrar;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Bind our helpers to singletons, so we can cache
        // some values for the duration of a request
        foreach ([
            CategoryHelper::class,
            RotationHelper::class,
            RoleHelper::class,
            SettingsHelper::class,
        ] as $singleton) {
            $this->app->singleton($singleton);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Registrar $charts): void
    {
         $charts->register([
             PurchaseHistoryChart::class,
             ItemSalesChart::class,
             ActivitySalesChart::class,
             IncomeHistoryChart::class,
         ]);
    }
}
