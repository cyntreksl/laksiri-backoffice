<?php

namespace App\Actions\CurrencyRate;

use App\Models\Currency;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteCurrencyRate
{
    use AsAction;

    public function handle(Currency $currency)
    {
        $currency->delete();
    }
}
