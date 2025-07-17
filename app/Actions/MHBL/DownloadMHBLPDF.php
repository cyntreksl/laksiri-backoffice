<?php

namespace App\Actions\MHBL;

use App\Actions\Setting\GetSettings;
use App\Models\MHBL;
use Barryvdh\DomPDF\Facade\Pdf;
use Lorisleiva\Actions\Concerns\AsAction;

class DownloadMHBLPDF
{
    use AsAction;

    public function handle(MHBL $mhbl, $hblsWithPackages)
    {
        // Build a flat package list from all HBLs
        $packages = collect($hblsWithPackages)->flatMap(function ($hbl) {
            if (! isset($hbl['packages']) || ! is_iterable($hbl['packages'])) {
                return [];
            }

            return collect($hbl['packages'])->map(function ($package) use ($hbl) {
                return array_merge(
                    is_array($package) ? $package : $package->toArray(),
                    [
                        'hbl_number' => $hbl['hbl_number'] ?? '',
                        'consignee_name' => $hbl['consignee_name'] ?? '',
                        'consignee_address' => $hbl['consignee_address'] ?? '',
                        'consignee_contact' => $hbl['consignee_contact'] ?? '',
                        'consignee_nic' => $hbl['consignee_nic'] ?? '',
                        'shipper_name' => $hbl['hbl_name'] ?? '',
                        'shipper_address' => $hbl['address'] ?? '',
                        'shipper_contact' => $hbl['contact_number'] ?? '',
                        'shipper_nic' => $hbl['nic'] ?? '',
                    ]
                );
            });
        })->values();

        $hblsCollection = collect($hblsWithPackages);

        $summary = [
            'total_packages' => $packages->count(),
            'total_quantity' => $packages->sum('quantity'),
            'freight_charge' => $hblsCollection->sum('freight_charge'),
            'destination_charge' => $hblsCollection->sum('destination_charge'),
            'bill_charge' => $hblsCollection->sum('bill_charge'),
            'grand_total' => $hblsCollection->sum('grand_total'),
            'paid_amount' => $hblsCollection->sum('paid_amount'),
            'total_volume' => $packages->sum('volume'),
            'total_weight' => $packages->sum('actual_weight'),
        ];

        $settings = GetSettings::run();
        $logoPath = $settings['logo_url'] ?? null;

        $pdf = Pdf::loadView('pdf.mhbl.hbl', [
            'mhbl' => $mhbl,
            'packages' => $packages,
            'settings' => $settings,
            'logoPath' => $logoPath,
            'summary' => $summary,
        ])->setPaper('a4');

        $filename = $mhbl->hbl_number.'.pdf';

        return $pdf->download($filename);
    }
}
