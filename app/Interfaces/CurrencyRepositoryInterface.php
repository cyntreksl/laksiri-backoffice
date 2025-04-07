<?php

namespace App\Interfaces;

use App\Models\Currency;

interface CurrencyRepositoryInterface
{
    public function createCurrency(array $data);

    public function updateCurrency(Currency $currency, array $data);

    public function destroyCurrency(Currency $currency);

    public function updateCurrencyRate(array $currencyIds, float $sl_rate);
}
