<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Barryvdh\DomPDF\Facade\Pdf;
use Lorisleiva\Actions\Concerns\AsAction;

class DownloadHBLInvoicePDF
{
    use AsAction;

    public function handle(HBL $hbl)
    {
        $pdf = Pdf::loadView('pdf.hbls.hblInvoice', [
            'hbl' => $hbl->load('packages'),
        ])->setPaper('a4');

        $filename = $hbl->hbl.'.pdf';

        return $pdf->download($filename);
    }
}
