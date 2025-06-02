<?php

namespace App\Actions\VesselSchedule;

use App\Actions\Setting\GetSettings;
use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\Browsershot\Browsershot;

class DownloadShipmentRelease
{
    use AsAction;

    public function handle(Container $container)
    {
        $template = view('pdf.shipment.release', [
            'container' => $container->load('branch', 'hbl_packages'),
            'logoPath' => GetSettings::run()['logo_url'] ?? null,
        ])->render();

        $filename = 'shipment_release_'.$container->reference.'.pdf';

        $filePath = storage_path("app/public/{$filename}");

        Browsershot::html($template)
            ->format('A4')
            ->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
