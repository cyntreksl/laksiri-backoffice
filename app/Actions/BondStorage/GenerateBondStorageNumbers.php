<?php

namespace App\Actions\BondStorage;

use App\Models\Container;
use App\Models\HBL;
use App\Models\HBLPackage;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateBondStorageNumbers
{
    use AsAction;

    public function handle(int $containerId, array $packages, array $manualHbls = []): array
    {
        $container = Container::findOrFail($containerId);
        $settings = Setting::first();

        $cargoType = strtoupper($container->cargo_type);
        $serialKey = $cargoType === 'SEA' ? 'bond_storage_sea_serial' : 'bond_storage_air_serial';

        $currentSerial = $settings->$serialKey ?? 1;

        $year = now()->format('y');
        $month = now()->format('m');
        $prefix = "{$year}{$month}";

        $generatedNumbers = [];
        $sequence = $currentSerial;

        // Group packages by HBL
        $packagesByHbl = collect($packages)->groupBy('hbl_id');

        // Process existing packages
        foreach ($packagesByHbl as $hblId => $hblPackages) {
            $packageCount = count($hblPackages);
            $sequenceFormatted = str_pad($sequence, 5, '0', STR_PAD_LEFT);

            foreach ($hblPackages as $index => $packageData) {
                $bondNumber = "{$prefix}/{$sequenceFormatted}-" . ($index + 1);

                HBLPackage::where('id', $packageData['id'])->update([
                    'bond_storage_number' => $bondNumber,
                ]);

                $generatedNumbers[] = [
                    'package_id' => $packageData['id'],
                    'hbl_id' => $hblId,
                    'bond_storage_number' => $bondNumber,
                ];
            }

            $sequence++;
        }

        // Process manual HBLs
        foreach ($manualHbls as $manualHbl) {
            $hbl = HBL::where('hbl_number', $manualHbl['hbl_number'])->first();

            if (!$hbl) {
                continue;
            }

            $packageCount = count($manualHbl['packages']);
            $sequenceFormatted = str_pad($sequence, 5, '0', STR_PAD_LEFT);

            foreach ($manualHbl['packages'] as $index => $packageData) {
                $bondNumber = "{$prefix}/{$sequenceFormatted}-" . ($index + 1);

                // Create or update package
                if (isset($packageData['id'])) {
                    HBLPackage::where('id', $packageData['id'])->update([
                        'bond_storage_number' => $bondNumber,
                    ]);

                    $generatedNumbers[] = [
                        'package_id' => $packageData['id'],
                        'hbl_id' => $hbl->id,
                        'bond_storage_number' => $bondNumber,
                    ];
                }
            }

            $sequence++;
        }

        // Update the serial number in settings
        $settings->update([
            $serialKey => $sequence,
        ]);

        // Log the generation
//        Log::info('Bond storage numbers generated', [
//            'container_id' => $containerId,
//            'cargo_type' => $cargoType,
//            'starting_serial' => $currentSerial,
//            'ending_serial' => $sequence - 1,
//            'total_hbls' => count($packagesByHbl) + count($manualHbls),
//            'generated_by' => Auth::id(),
//        ]);

        return [
            'generated_numbers' => $generatedNumbers,
            'starting_serial' => $currentSerial,
            'ending_serial' => $sequence - 1,
            'total_generated' => count($generatedNumbers),
        ];
    }
}
