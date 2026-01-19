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

        $examination = Examination::where('token_id', $token->id)->first();

        // Filter only released packages
        $releasedPackages = $hbl->packages->filter(function ($package) {
            return $package->release_status === 'released';
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
            'by' => GetUserById::run(auth()->id())->name,
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
