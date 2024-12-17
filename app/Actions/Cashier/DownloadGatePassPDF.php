<?php

namespace App\Actions\Cashier;

use App\Actions\HBL\GetHBLByIdWithPackages;
use App\Services\GatePassChargesService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;
use NumberFormatter;

class DownloadGatePassPDF
{
    use AsAction;

    public function handle($hbl)
    {
        $hbl = GetHBLByIdWithPackages::run($hbl);
        $container = $hbl->packages[0]->containers()->withoutGlobalScopes()->first();

        $arrivalDatesCount = $container ? Carbon::parse($container['estimated_time_of_arrival'])->diffInDays(Carbon::now()->startOfDay(), false) : 0;

        $service = new GatePassChargesService($hbl['cargo_type']);
        //        $service = new GatePassChargesService('Air Cargo');

        $grand_volume = $hbl->packages()->withoutGlobalScopes()->sum('volume');
        $grand_weight = $hbl->packages()->withoutGlobalScopes()->sum('weight');

        $charges = [
            'port_charge' => $service->portCharge($grand_volume),
            'handling_charge' => $service->handlingCharge($hbl->packages()->count()),
            'storage_charge' => $service->bondCharge($grand_volume, $grand_weight),
            'dmg_charge' => $service->demurrageCharge($arrivalDatesCount, $grand_volume, $grand_weight),
            'total' => $service->portCharge($grand_volume)['amount'] + $service->handlingCharge($hbl->packages()->count())['amount'] + $service->bondCharge($grand_volume, $grand_weight)['amount'] + $service->demurrageCharge(28, $grand_volume, $grand_weight)['amount'],
            'do_charge' => $hbl->do_charge,
            'stamp_charge' => ($service->portCharge($grand_volume)['amount'] + $service->handlingCharge($hbl->packages()->count())['amount'] + $service->bondCharge($grand_volume, $grand_weight)['amount'] + $service->demurrageCharge(28, $grand_volume, $grand_weight)['amount']) > 25000 ? 25.00 : 00.00,
        ];

        $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        $total_in_word = strtoupper($formatter->format($charges['total']));

        $data = [
            'clearing_time' => date('H:i:s'),
            'date' => date('Y-m-d'),
            'vessel' => $container,
            'hbl' => $hbl,
            'grand_volume' => $hbl->packages->sum('volume'),
            'charges' => $charges,
            'total_in_word' => $total_in_word,
            'by' => Auth::user()->name,
        ];
        $pdf = Pdf::loadView('pdf.cashier.gatePass', ['data' => $data, 'hbl' => $hbl])->setPaper('a4');

        $filename = 'RECEIPT'.$hbl['reference'].'.pdf';

        return $pdf->stream($filename);
    }
}
