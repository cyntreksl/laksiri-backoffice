<?php

namespace App\Actions\Container\Unloading;

use App\Models\HBLPackage;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class AssignBondStorageNumber
{
    use AsAction;

    public function handle(Collection|array $hblGroups): void
    {
        $year = now()->format('y');
        $month = now()->format('m');
        $prefix = "{$year}{$month}";

        foreach ($hblGroups as $hblId => $packagesInHbl) {
            // Get the last bond storage number for this month
            $lastBondNumber = HBLPackage::where('bond_storage_number', 'like', "{$prefix}/%")
                ->orderByDesc('bond_storage_number')
                ->value('bond_storage_number');

            // Determine next sequence
            if ($lastBondNumber) {
                preg_match('/'.$prefix.'\/(\d{4})/', $lastBondNumber, $matches);
                $sequence = isset($matches[1]) ? (int) $matches[1] + 1 : 1;
            } else {
                $sequence = 1;
            }

            $sequenceFormatted = str_pad($sequence, 4, '0', STR_PAD_LEFT);

            // Assign a bond number for each package in this HBL
            foreach (collect($packagesInHbl)->values() as $index => $package) {
                $bondNumber = "{$prefix}/{$sequenceFormatted}-".($index + 1);

                HBLPackage::where('id', $package['id'])->update([
                    'bond_storage_number' => $bondNumber,
                ]);
            }
        }
    }
}
