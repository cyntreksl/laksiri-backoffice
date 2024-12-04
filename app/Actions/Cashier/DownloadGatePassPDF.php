<?php

namespace App\Actions\Cashier;

use App\Actions\HBL\GetHBLByIdWithPackages;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;
use NumberFormatter;

class DownloadGatePassPDF
{
    use AsAction;

    public function handle($hbl)
    {
        $hbl = GetHBLByIdWithPackages::run($hbl)->load('packages.containers');

        $charges = [
            'port_charge' => [
                'rate' => 600.00,
                'amount' => 00.00,
            ],
            'handling_charge' => [
                'rate' => 00.00,
                'amount' => 00.00,
            ],
            'storage_charge' => [
                'rate' => 270.00,
                'amount' => 00.00,
            ],
            'dmg_charge' => [
                'rate' => 00.00,
                'amount' => 4310.00,
            ],
            'do_charge' => 2500.00,
            'stamp_charge' => 00.00,
            'total' => 6810.16,
        ];

        $formatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        $total_in_word = strtoupper($formatter->format($charges['total']));

        $data = [
            'clearing_time' => date('H:i:s'),
            'date' => date('Y-m-d'),
            'vessel' => $hbl->packages[0]->containers[0],
            'hbl' => $hbl,
            'grand_volume' => $hbl->packages->sum('volume'),
            'charges' => $charges,
            'total_in_word' => $total_in_word,
            'by' => Auth::user()->name,
        ];
        $pdf = Pdf::loadView('pdf.cashier.gatePass', ['data' => $data, 'hbl' => $hbl])->setPaper('a4');

        $filename = 'RECEIPT'.$hbl['reference'].'.pdf';

        return $pdf->download($filename);
    }
}
