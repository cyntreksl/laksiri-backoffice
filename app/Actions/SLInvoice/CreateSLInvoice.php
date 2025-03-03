<?php

namespace App\Actions\SLInvoice;

use App\Models\SLInvoice;
use App\Services\GatePassChargesService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateSLInvoice
{
    use AsAction;

    public function handle($hbl): SLInvoice
    {
        $container = $this->getContainer($hbl);
        $arrivalDatesCount = $this->calculateArrivalDatesCount($container);

        $service = new GatePassChargesService($hbl['cargo_type']);

        $grandVolume = $hbl->packages()->withoutGlobalScopes()->sum('volume');
        $grandWeight = $hbl->packages()->withoutGlobalScopes()->sum('weight');

        $charges = $this->calculateCharges($service, $grandVolume, $grandWeight, $hbl->packages()->count(), $arrivalDatesCount);

        $data = $this->prepareInvoiceData($hbl, $container, $grandVolume, $grandWeight, $charges);

        return $hbl->slInvoices()->create($data);
    }

    private function getContainer($hbl)
    {
        return $hbl->packages[0]->containers()->withoutGlobalScopes()->first() ?? $hbl->packages[0]->duplicate_containers()->withoutGlobalScopes()->first();
    }

    private function calculateArrivalDatesCount($container): int
    {
        return $container ? Carbon::parse($container['estimated_time_of_arrival'])->diffInDays(Carbon::now()->startOfDay(), false) : 0;
    }

    private function calculateCharges(GatePassChargesService $service, float $grandVolume, float $grandWeight, int $packageCount, int $arrivalDatesCount): array
    {
        return [
            'port_charge' => $service->portCharge($grandVolume),
            'handling_charge' => $service->handlingCharge($packageCount),
            'storage_charge' => $service->bondCharge($grandVolume, $grandWeight),
            'dmg_charge' => $service->demurrageCharge($arrivalDatesCount, $grandVolume, $grandWeight),
        ];
    }

    private function prepareInvoiceData($hbl, $container, float $grandVolume, float $grandWeight, array $charges): array
    {
        $totalAmount = $charges['port_charge']['amount']
            + $charges['handling_charge']['amount']
            + $charges['storage_charge']['amount']
            + $charges['dmg_charge']['amount'];

        return [
            'clearing_time' => now()->format('H:i:s'),
            'date' => now()->format('Y-m-d'),
            'container_id' => $container->id ?? null,
            'grand_volume' => $grandVolume,
            'grand_weight' => $grandWeight,
            'port_charge_rate' => $charges['port_charge']['rate'],
            'port_charge_amount' => $charges['port_charge']['amount'],
            'handling_charge_rate' => $charges['handling_charge']['rate'],
            'handling_charge_amount' => $charges['handling_charge']['amount'],
            'storage_charge_rate' => $charges['storage_charge']['rate'],
            'storage_charge_amount' => $charges['storage_charge']['amount'],
            'dmg_charge_rate' => $charges['dmg_charge']['rate'],
            'dmg_charge_amount' => $charges['dmg_charge']['amount'],
            'total' => $totalAmount,
            'do_charge' => $hbl->do_charge,
            'stamp_charge' => $totalAmount > 25000 ? 25.00 : 0.00,
            'created_by' => Auth::id(),
        ];
    }
}
