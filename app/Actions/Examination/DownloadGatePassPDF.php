<?php

namespace App\Actions\Examination;

use App\Actions\HBL\GetHBLByIdWithPackages;
use App\Actions\User\GetUserById;
use App\Models\CustomerQueue;
use App\Models\Examination;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;
use Wnx\SidecarBrowsershot\BrowsershotLambda;

class DownloadGatePassPDF
{
    use AsAction;

    public function handle($hbl, CustomerQueue $customerQueue)
    {
        $hbl = GetHBLByIdWithPackages::run($hbl);

        $token = $customerQueue->token;

        $examination = Examination::where('token_id', $token->id)->latest()->first();

        if (!$examination) {
            abort(404, 'No examination record found.');
        }

        // Get only packages that were released in THIS examination
        // Check the released_packages from the examination record
        $releasedPackageIds = array_keys(array_filter($examination->released_packages ?? []));

        if (empty($releasedPackageIds)) {
            abort(404, 'No packages were released in this examination.');
        }

        // Filter packages to only those released in this examination
        $releasedPackages = $hbl->packages->filter(function ($package) use ($releasedPackageIds) {
            return in_array($package->id, $releasedPackageIds);
        });

        // If no released packages, return error
        if ($releasedPackages->isEmpty()) {
            abort(404, 'No released packages found for this gate pass.');
        }

        $container = $releasedPackages->first()->containers()->withoutGlobalScopes()->first();

        $data = [
            'token' => $token->token,
            'time' => Carbon::parse($examination->released_at)->toTimeString(),
            'date' => Carbon::parse($examination->released_at)->toDateString(),
            'vessel' => $container,
            'hbl' => $hbl,
            'releasedPackages' => $releasedPackages,
            'userId' => GetUserById::run($token->customer_id)->name,
            'by' => GetUserById::run($examination->released_by)->name,
            'serial' => $customerQueue->id,
        ];

        $template = view('pdf.examination.gate-pass', [
            'logoPath' => asset('images/app-logo.png') ?? null,
            'data' => $data,
            'hbl' => $hbl,
        ])->render();

        $filename = 'GATE-PASS-'.$hbl['reference'].'.pdf';

        $filePath = storage_path("app/public/{$filename}");

        BrowsershotLambda::html($template)
            ->showBackground()
            ->format('A4')
            ->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
