<?php

namespace App\Actions\Cashier;

use App\Actions\HBL\GetHBLByIdWithPackages;
use Lorisleiva\Actions\Concerns\AsAction;

class AutoPrintCashierReceipt
{
    use AsAction;

    public function handle($hbl)
    {
        $hbl = GetHBLByIdWithPackages::run($hbl);
        
        $pdfUrl = route('hbls.streamPOSReceipt', ['hbl' => $hbl->id]);
        
        return view('pdf.cashier.receipt-print', [
            'pdfUrl' => $pdfUrl,
            'hblReference' => $hbl->reference,
        ]);
    }
}
