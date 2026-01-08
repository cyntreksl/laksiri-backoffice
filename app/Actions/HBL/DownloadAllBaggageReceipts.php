<?php

namespace App\Actions\HBL;

use App\Actions\Setting\GetSettings;
use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;
use Wnx\SidecarBrowsershot\BrowsershotLambda;

class DownloadAllBaggageReceipts
{
    use AsAction;

    public function handle($container)
    {
        // Get HBL IDs from the container's packages
        $hblIds = $container->hbl_packages()->pluck('hbl_id')->unique();

        // Get HBLs with their packages and containers
        $hbls = HBL::whereIn('id', $hblIds)
            ->with(['packages', 'containers'])
            ->get();

        if ($hbls->isEmpty()) {
            abort(404, 'No HBLs found for this container');
        }

        $settings = GetSettings::run();
        $logoPath = asset('images/app-logo.png') ?? null;

        $template = view('exports.baggage-bulk', [
            'hbls' => $hbls,
            'container' => $container,
            'settings' => $settings,
            'logoPath' => $logoPath,
        ])->render();

        $filename = 'baggage-receipts-'.$container->reference.'.pdf';

        $filePath = storage_path("app/public/{$filename}");

        BrowsershotLambda::html($template)
            ->showBackground()
            ->format('A4')
            ->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
