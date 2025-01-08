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
        $container = $hbl->packages[0]->containers()->withoutGlobalScopes()->first();

        $arrivalDatesCount = $container ? Carbon::parse($container['estimated_time_of_arrival'])->diffInDays(Carbon::now()->startOfDay(), false) : 0;

        $service = new GatePassChargesService($hbl['cargo_type']);

        $grand_volume = $hbl->packages()->withoutGlobalScopes()->sum('volume');
        $grand_weight = $hbl->packages()->withoutGlobalScopes()->sum('weight');

        $port_charge = $service->portCharge($grand_volume);
        $handling_charge = $service->handlingCharge($hbl->packages()->count());
        $storage_charge = $service->bondCharge($grand_volume, $grand_weight);
        $dmg_charge = $service->demurrageCharge($arrivalDatesCount, $grand_volume, $grand_weight);

        $data = [
            'clearing_time' => date('H:i:s'),
            'date' => date('Y-m-d'),
            'container_id' => $container->id ?? null,
            'grand_volume' => $grand_volume,
            'grand_weight' => $grand_weight,
            'port_charge_rate' => $port_charge['rate'],
            'port_charge_amount' => $port_charge['amount'],
            'handling_charge_rate' => $handling_charge['rate'],
            'handling_charge_amount' => $handling_charge['amount'],
            'storage_charge_rate' => $storage_charge['rate'],
            'storage_charge_amount' => $storage_charge['amount'],
            'dmg_charge_rate' => $dmg_charge['rate'],
            'dmg_charge_amount' => $dmg_charge['amount'],
            'total' => $service->portCharge($grand_volume)['amount'] + $service->handlingCharge($hbl->packages()->count())['amount'] + $service->bondCharge($grand_volume, $grand_weight)['amount'] + $service->demurrageCharge(28, $grand_volume, $grand_weight)['amount'],
            'do_charge' => $hbl->do_charge,
            'stamp_charge' => ($service->portCharge($grand_volume)['amount'] + $service->handlingCharge($hbl->packages()->count())['amount'] + $service->bondCharge($grand_volume, $grand_weight)['amount'] + $service->demurrageCharge(28, $grand_volume, $grand_weight)['amount']) > 25000 ? 25.00 : 00.00,
            'created_by' => Auth::id(),
        ];

        return $hbl->slInvoices()->create($data);
    }
}
