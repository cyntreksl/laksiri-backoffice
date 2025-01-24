<?php

namespace App\Actions\HBL;

use App\Actions\Setting\GetSettings;
use App\Models\Container;
use App\Models\HBL;
use Barryvdh\DomPDF\Facade\Pdf;
use Lorisleiva\Actions\Concerns\AsAction;

class DownloadBaggagePDF
{
    use AsAction;

    public function handle(HBL $hbl)
    {

        $pdf = Pdf::loadView('exports.baggage', [
            'hbl' => $hbl,
            'settings' => GetSettings::run(),
            'logoPath' => GetSettings::run()['logo_url'] ?? null,
        ])->setPaper('a4');

        $filename = $hbl->hbl.'.pdf';

        return $pdf->stream($filename);
    }
}
