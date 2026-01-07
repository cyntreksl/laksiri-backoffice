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

        $service = new GatePassChargesService($hbl->cargo_type);

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
        // If destination payment is done, exclude destination charges from invoice
        $isDestinationChargesPaid = $hbl->is_destination_charges_paid ?? false;

        $portChargeAmount = $isDestinationChargesPaid ? 0.00 : $charges['port_charge']['amount'];
        $handlingChargeAmount = $isDestinationChargesPaid ? 0.00 : $charges['handling_charge']['amount'];
        $storageChargeAmount = $isDestinationChargesPaid ? 0.00 : $charges['storage_charge']['amount'];
        $dmgChargeAmount = $isDestinationChargesPaid ? 0.00 : $charges['dmg_charge']['amount'];

        $totalAmount = $portChargeAmount
            + $handlingChargeAmount
            + $storageChargeAmount
            + $dmgChargeAmount;

        return [
            'clearing_time' => now()->format('H:i:s'),
            'date' => now()->format('Y-m-d'),
            'container_id' => $container->id ?? null,
            'grand_volume' => $grandVolume,
            'grand_weight' => $grandWeight,
            'port_charge_rate' => $isDestinationChargesPaid ? 0.00 : $charges['port_charge']['rate'],
            'port_charge_amount' => $portChargeAmount,
            'handling_charge_rate' => $isDestinationChargesPaid ? 0.00 : $charges['handling_charge']['rate'],
            'handling_charge_amount' => $handlingChargeAmount,
            'storage_charge_rate' => $isDestinationChargesPaid ? 0.00 : $charges['storage_charge']['rate'],
            'storage_charge_amount' => $storageChargeAmount,
            'dmg_charge_rate' => $isDestinationChargesPaid ? 0.00 : $charges['dmg_charge']['rate'],
            'dmg_charge_amount' => $dmgChargeAmount,
            'total' => $totalAmount,
            'do_charge' => $hbl->do_charge,
            'stamp_charge' => $totalAmount > 25000 ? 25.00 : 0.00,
            'created_by' => Auth::id(),
        ];
    }
}
