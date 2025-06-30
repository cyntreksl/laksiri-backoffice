<?php

namespace App\Providers;

use App\Services\PaymentCalculation\FreightChargeCalculator;
use App\Services\PaymentCalculation\PackagePaymentCalculator;
use App\Services\PaymentCalculation\PaymentCalculatorInterface;
use App\Services\PaymentCalculation\PriceRuleProcessor;
use App\Services\PaymentCalculation\StandardPaymentCalculator;
use Illuminate\Support\ServiceProvider;

class PaymentCalculationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FreightChargeCalculator::class);
        $this->app->singleton(PriceRuleProcessor::class);

        $this->app->singleton(PackagePaymentCalculator::class);

        $this->app->singleton(StandardPaymentCalculator::class, function ($app) {
            return new StandardPaymentCalculator(
                $app->make(FreightChargeCalculator::class),
                $app->make(PriceRuleProcessor::class)
            );
        });

        $this->app->bind(PaymentCalculatorInterface::class, function ($app) {
            // Default to standard calculator, but this can be configured based on context
            return $app->make(StandardPaymentCalculator::class);
        });
    }

    public function boot(): void
    {
        //
    }
}
