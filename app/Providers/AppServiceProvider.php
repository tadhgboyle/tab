<?php

namespace App\Providers;

use App\Charts\ActivitySalesChart;
use App\Charts\IncomeHistoryChart;
use App\Charts\ItemSalesChart;
use App\Charts\PurchaseHistoryChart;
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
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Charts $charts): void
    {
        $charts->register([
            PurchaseHistoryChart::class,
            ItemSalesChart::class,
            ActivitySalesChart::class,
            IncomeHistoryChart::class,
        ]);
    }
}
