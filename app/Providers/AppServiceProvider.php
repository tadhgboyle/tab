<?php

namespace App\Providers;

use NumberFormatter;
use Cknow\Money\Money;
use App\Helpers\TaxHelper;
use App\Helpers\RoleHelper;
use App\Charts\IncomeInfoChart;
use App\Helpers\CategoryHelper;
use App\Helpers\RotationHelper;
use App\Helpers\SettingsHelper;
use ConsoleTVs\Charts\Registrar;
use App\Charts\OrderHistoryChart;
use App\Charts\ProductSalesChart;
use App\Charts\ActivitySalesChart;
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
            TaxHelper::class,
        ] as $singleton) {
            $this->app->singleton($singleton);
        }

        // Create macro on Money to format it for inputs
        Money::macro('formatForInput', function () {
            $formatted = $this->format(null, null, NumberFormatter::DECIMAL);

            if (is_string($formatted)) {
                $formatted = str_replace(',', '', $formatted);
            }

            return number_format($formatted, 2, '.', '');
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Registrar $charts): void
    {
        $charts->register([
            OrderHistoryChart::class,
            ProductSalesChart::class,
            ActivitySalesChart::class,
            IncomeInfoChart::class,
        ]);
    }
}
