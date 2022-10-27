<?php

namespace App\Providers;

use App\Charts\ItemSalesChart;
use App\Charts\ActivitySalesChart;
use App\Charts\IncomeHistoryChart;
use App\Charts\PurchaseHistoryChart;
use App\Helpers\CategoryHelper;
use App\Helpers\RoleHelper;
use App\Helpers\RotationHelper;
use App\Helpers\SettingsHelper;
use Illuminate\Support\ServiceProvider;
use ConsoleTVs\Charts\Registrar as Charts;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Bind our helpers to singletons so we can cache
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
    public function boot(): void
    {
        // $charts->register([
        //     PurchaseHistoryChart::class,
        //     ItemSalesChart::class,
        //     ActivitySalesChart::class,
        //     IncomeHistoryChart::class,
        // ]);
    }
}
