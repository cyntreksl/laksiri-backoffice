<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use App\Models\Scopes\BranchScope;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Lorisleiva\Actions\Concerns\AsAction;

class DownloadHBLPDF
{
    use AsAction;

    public function handle(HBL $hbl)
    {
        $hbl->loadMissing('branch');

        $settings = Setting::withoutGlobalScope(BranchScope::class)
            ->where('branch_id', $hbl->branch_id)
            ->first();

        $pdf = Pdf::loadView('pdf.hbls.hbl', [
            'hbl' => $hbl,
            'settings' => $settings,
            'logoPath' => $settings->logo_url ?? null,
        ])->setPaper('a4');

        $filename = $hbl->hbl_number.'.pdf';

        return $pdf->download($filename);
    }
}
