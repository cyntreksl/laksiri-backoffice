<?php

namespace App\Actions\Courier;

use App\Actions\Setting\GetSettings;
use App\Models\Courier;
use Barryvdh\DomPDF\Facade\Pdf;
use Lorisleiva\Actions\Concerns\AsAction;

class DownloadCourierPDF
{
    use AsAction;

    public function handle(Courier $courier)
    {
        $courier->load(['packages', 'courierAgent', 'branch']);

        $pdf = Pdf::loadView('pdf.courier.courier', [
            'hbl' => $courier,
            'settings' => GetSettings::run(),
            'logoPath' => GetSettings::run()['logo_url'] ?? null,
        ])->setPaper('a4');

        return $pdf->stream("courier-{$courier->courier_number}.pdf");
    }
}
