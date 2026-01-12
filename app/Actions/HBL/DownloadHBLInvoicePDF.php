<?php

namespace App\Actions\HBL;

use App\Actions\Setting\GetSettings;
use App\Actions\User\GetUserById;
use App\Models\HBL;
use Barryvdh\DomPDF\Facade\Pdf;
use Lorisleiva\Actions\Concerns\AsAction;

class DownloadHBLInvoicePDF
{
    use AsAction;

    public function handle(HBL $hbl)
    {
        $pdf = Pdf::loadView('pdf.hbls.hblInvoice', [
            'hbl' => $hbl->load(['packages', 'destinationCharge', 'branch']),
            'settings' => GetSettings::run(),
            'logoPath' => GetSettings::run()['logo_url'] ?? null,
            'invoice_header_title' => GetSettings::run()['invoice_header_title'] ?? null,
            'salesman' => GetUserById::run($hbl['created_by']),
        ])->setPaper('a4');

        $filename = $hbl->hbl_number.'invoice'.'.pdf';

        return $pdf->download($filename);
    }
}
