<?php

namespace App\Actions\HBL;

use App\Services\GatePassChargesService;
use App\Services\PriceCalculationService;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLDestinationTotalSummary
{
    use AsAction;

    public function handle($hbl)
    {
        dd($hbl);
        $service = new GatePassChargesService($hbl['cargo_type']);

        $container = $this->getContainer($hbl);
        $arrivalDatesCount = $this->calculateArrivalDatesCount($container);

        return [
            'handlingCharges' => $service->handlingCharge($hbl->packages->count()),
            'slpaCharge' => $service->portCharge($hbl->packages->sum('volume')),
            'bondCharge' => $service->bondCharge($hbl->packages->sum('volume'), $hbl->packages->sum('weight')),
            'demurrageCharge' => $container ?
                $service->demurrageCharge($arrivalDatesCount, $hbl->packages->sum('volume'), $hbl->packages->sum('weight'))
                : [
                    'rate' => 0,
                    'amount' => 0
                ],
            'dOCharge' => $service->dOCharge(),
        ];
    }

    private function getContainer($hbl)
    {
        return $hbl->packages[0]->containers()->withoutGlobalScopes()->first() ?? $hbl->packages[0]->duplicate_containers()->withoutGlobalScopes()->first();
    }

    private function calculateArrivalDatesCount($container): int
    {
        return $container ? Carbon::parse($container['estimated_time_of_arrival'])->diffInDays(Carbon::now()->startOfDay(), false) : 0;
    }

}
