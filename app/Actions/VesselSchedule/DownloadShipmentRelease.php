<?php

namespace App\Actions\VesselSchedule;

use App\Actions\Setting\GetSettings;
use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;
use Wnx\SidecarBrowsershot\BrowsershotLambda;

class DownloadShipmentRelease
{
    use AsAction;

    public function handle(Container $container)
    {
        // Get all containers with the same BL number, including the count of their HBL packages
        $containers = Container::where('bl_number', $container->bl_number)
            ->withCount('hbl_packages')
            ->get();

        $template = view('pdf.shipment.release', [
            'container' => $container->load('branch', 'hbl_packages'),
            'logoPath' => GetSettings::run()['logo_url'] ?? null,
            'containers' => $containers,
        ])->render();

        $filename = 'shipment_release_'.$container->reference.'.pdf';

        $filePath = storage_path("app/public/{$filename}");

        BrowsershotLambda::html($template)
            ->format('A4')
            ->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
