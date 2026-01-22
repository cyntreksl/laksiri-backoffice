<?php

namespace App\Actions\HBL\HBLPackage;

use App\Models\HBL;
use App\Models\Scopes\BranchScope;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPackagesByReference
{
    use AsAction;

    public function handle(string $reference)
    {
        $hbl = HBL::withoutGlobalScopes()->with([
            'packages' => function ($query) {
                $query->withoutGlobalScope(BranchScope::class)
                    // Only get packages that have been released from bonded area
                    ->where('release_status', 'released');
            },
        ])
            ->where('reference', $reference)->firstOrFail();

        $packages = $hbl->packages->map(function ($package) {
            return [
                'id' => $package->id,
                'package_type' => $package->package_type,
                'quantity' => $package->quantity,
                'length' => $package->length,
                'width' => $package->width,
                'height' => $package->height,
                'weight' => $package->weight,
                'volume' => $package->volume,
                'release_status' => $package->release_status,
                'released_at' => $package->released_at?->format('Y-m-d H:i:s'),
                'released_by' => $package->releasedByUser?->name,
                'bond_storage_number' => $package->bond_storage_number,
                'remarks' => $package->remarks,
            ];
        });

        return response()->json($packages);
    }
}
