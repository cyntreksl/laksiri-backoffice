<?php

namespace App\Actions\HBL;

use App\Services\GatePassChargesService;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLDestinationTotalSummary
{
    use AsAction;

    public function handle($hbl)
    {
        $service = new GatePassChargesService($hbl['cargo_type']);

        $container = $this->getContainer($hbl);
        $arrivalDatesCount = $this->calculateArrivalDatesCount($container);
        $vat = $service->vatCharge($hbl)['rate'];
        $handlingCharges = $service->handlingCharge($hbl->packages->count())['amount'] * (1 + $vat / 100);
        $slpaCharge = $service->portCharge($hbl->packages->sum('volume'))['amount'] * (1 + $vat / 100);
        $bondCharge = $service->bondCharge($hbl->packages->sum('volume'), $hbl->packages->sum('weight'))['amount'] * (1 + $vat / 100);
        $demurrageCharge = $container ?
        $service->demurrageCharge($arrivalDatesCount, $hbl->packages->sum('volume'), $hbl->packages->sum('weight'))['amount'] * (1 + $vat / 100)
        : 0.00;
        $dOCharge = $service->dOCharge($hbl)['amount'];

        return [
            'handlingCharges' => $handlingCharges,
            'slpaCharge' => $slpaCharge,
            'bondCharge' => $bondCharge,
            'demurrageCharge' => $demurrageCharge,
            'dOCharge' => $dOCharge,
            'vatCharge' => $vat,
            'totalAmount' => $handlingCharges + $slpaCharge + $bondCharge + $demurrageCharge + $dOCharge,
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
