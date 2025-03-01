<?php

namespace App\Actions\HBL;



use App\Models\HBL;
use App\Services\PriceCalculationService;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLTotalSummary
{
    use AsAction;

    public function handle($hbl): HBL
    {
        $totalSummary = new PriceCalculationService();
        $summary = $totalSummary->hblPriceSummary($hbl);
        dd($summary);
    }
}
