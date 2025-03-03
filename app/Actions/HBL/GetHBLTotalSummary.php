<?php

namespace App\Actions\HBL;

use App\Services\PriceCalculationService;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLTotalSummary
{
    use AsAction;

    public function handle($hbl)
    {
        $totalSummary = new PriceCalculationService;

        return $totalSummary->hblPriceSummary($hbl);
    }
}
