<?php

namespace App\Actions\CurrencyRate;

use App\Models\Currency;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateCurrencyRate
{
    use AsAction;

    public function handle(Currency $currencyRate, array $data)
    {
        return $currencyRate->update($data);
    }
}
