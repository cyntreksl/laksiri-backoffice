<?php

namespace App\Actions\CurrencyRate;

use App\Models\Currency;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateCurrenciesRates
{
    use AsAction;

    /**
     * Update the SL Rate for given currency IDs.
     *
     * @return int Number of records updated
     */
    public function handle(array $currencyIds, float $sl_rate): int
    {
        return Currency::whereIn('id', $currencyIds)
            ->update(['sl_rate' => $sl_rate]);
    }
}
