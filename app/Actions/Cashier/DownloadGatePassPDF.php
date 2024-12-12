<?php

namespace App\Actions\Cashier;

use App\Actions\HBL\GetHBLByIdWithPackages;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Services\GatePassChargesService;
use NumberFormatter;

class DownloadGatePassPDF
{
    use AsAction;

    protected $gatePassChargesService;

    /**
     * Constructor to inject the GatePassChargesService.
     */
    public function __construct(GatePassChargesService $gatePassChargesService)
    {
        $this->gatePassChargesService = $gatePassChargesService;
    }

    public function handle($hbl)
    {
        $hbl = GetHBLByIdWithPackages::run($hbl);
        $container = $hbl->packages[0]->containers()->withoutGlobalScopes()->first();
        
        $arrivalDatesCount = Carbon::parse($container['estimated_time_of_arrival'])->diffInDays(Carbon::now()->startOfDay(), false);

        dd($container['estimated_time_of_arrival'],$arrivalDatesCount);

        $charges = [
            'port_charge' => $this->gatePassChargesService->portCharge(),
            'handling_charge' => $this->gatePassChargesService->portCharge(),
            'storage_charge' => $this->gatePassChargesService->bondCharge(),
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
