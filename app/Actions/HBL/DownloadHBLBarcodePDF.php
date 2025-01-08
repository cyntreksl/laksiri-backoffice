<?php

namespace App\Actions\HBL;

use App\Actions\Setting\GetSettings;
use App\Models\HBL;
use Barryvdh\DomPDF\Facade\Pdf;
use Lorisleiva\Actions\Concerns\AsAction;

class DownloadHBLBarcodePDF
{
    use AsAction;

    public function handle(HBL $hbl)
    {
        $customPaper = [0, 0, 283.80, 567.00];
        $pdf = Pdf::loadView('pdf.hbls.hblBarcode', [
            'hbl' => $hbl->load('packages'),
            'logoPath' => GetSettings::run()['logo_url'] ?? null,
            'mhbl' => $hbl->mhbl ? ($hbl->mhbl->hbl_number ?? $hbl->mhbl->reference) : null,
        ])->setPaper($customPaper);

        $filename = $hbl->hbl.'.pdf';

        return $pdf->download($filename);
    }
}
