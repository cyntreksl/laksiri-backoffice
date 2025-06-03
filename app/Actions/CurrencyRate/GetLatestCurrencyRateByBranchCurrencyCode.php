<?php

namespace App\Actions\CurrencyRate;

use App\Models\Currency;
use Lorisleiva\Actions\Concerns\AsAction;

class GetLatestCurrencyRateByBranchCurrencyCode
{
    use AsAction;

    public function handle(string $currencyCode): Currency
    {
        return Currency::where('currency_symbol', $currencyCode)->latest()->first();
    }
}
