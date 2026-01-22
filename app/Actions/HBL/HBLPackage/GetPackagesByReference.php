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
        // Get status filter from request (default to 'released' for examination)
        $statusFilter = request()->query('status', 'released');
        
        $hbl = HBL::withoutGlobalScopes()->with([
            'packages' => function ($query) use ($statusFilter) {
                $query->withoutGlobalScope(BranchScope::class);
                
                // Apply status filter
                if ($statusFilter === 'held') {
                    // For bonded area - show packages that are not yet released (pending or held)
                    $query->whereIn('release_status', ['pending', 'held']);
                } elseif ($statusFilter === 'released') {
                    // For examination - show released packages
                    $query->where('release_status', 'released');
                } elseif ($statusFilter === 'all') {
                    // Show all packages
                    // No filter
                }
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
