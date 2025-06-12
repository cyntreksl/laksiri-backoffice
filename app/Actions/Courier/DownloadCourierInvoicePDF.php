<?php

namespace App\Actions\Courier;

use App\Actions\Setting\GetSettings;
use App\Actions\User\GetUserById;
use App\Models\Courier;
use Barryvdh\DomPDF\Facade\Pdf;
use Lorisleiva\Actions\Concerns\AsAction;

class DownloadCourierInvoicePDF
{
    use AsAction;

    public function handle(Courier $courier)
    {
        $courier->load(['packages', 'courierAgent']);

        $pdf = Pdf::loadView('pdf.courier.invoice', [
            'hbl' => $courier,
            'settings' => GetSettings::run(),
            'logoPath' => GetSettings::run()['logo_url'] ?? null,
            'invoice_header_title' => GetSettings::run()['invoice_header_title'] ?? null,
            'salesman' => GetUserById::run($courier['created_by']),
        ])->setPaper('a4');

        return $pdf->stream("courier-invoice-{$courier->courier_number}.pdf");
    }
}
