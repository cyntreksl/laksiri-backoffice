<?php

namespace App\Actions\CurrencyRate;

use App\Models\Currency;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateCurrencyRate
{
    use AsAction;

    public function handle(array $data): Currency
    {
        $currencyData = [
            ...$data,
            'created_by' => Auth::user()->id,
        ];

        return Currency::create($currencyData);
    }
}
