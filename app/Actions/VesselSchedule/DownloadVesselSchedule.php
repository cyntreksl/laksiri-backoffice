<?php

namespace App\Actions\VesselSchedule;

use App\Models\VesselSchedule;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class DownloadVesselSchedule
{
    use AsAction;

    public function handle(VesselSchedule $vesselSchedule)
    {
        $formattedDate = Carbon::now()->format('dS').' '.strtoupper(Carbon::now()->format('F')).' '.Carbon::now()->format('Y');

        $containerData = [];
        foreach ($vesselSchedule->clearanceContainers->load('branch', 'duplicate_hbl_packages') as $container) {
            $containerData[] = [
                'vessel_name' => $container->vessel_name ?? '',
                'agent' => strtoupper($container->branch->name ?? ''),
                'container_no' => $container->container_no ?? '',
                'bl_no' => $container->bl_number ?? '',
                'release' => '()',
                'quantity' => '1X'.count($container->duplicate_hbl_packages),
                'ship_agent' => $container->shipping_line ?? '',
                'date' => $container->estimated_time_of_arrival ?? '',
            ];
        }
        $pdf = Pdf::loadView('pdf.vesselSchedule.vesselSchedule', [
            'formattedDate' => $formattedDate,
            'containers' => $containerData,
        ])->setPaper('a4', 'landscape');

        $filename = 'vessel-schedule.pdf';

        return $pdf->download($filename);
    }
}
