<?php

namespace App\Actions\ThirdPartyShipment;

use App\Models\TmpHbl;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTmpHblsBySession
{
    use AsAction;

    public function handle(string $sessionId): array
    {
        $hbls = TmpHbl::where('session_id', $sessionId)
            ->with(['packages' => function ($query) use ($sessionId) {
                $query->where('session_id', $sessionId);
            }])
            ->get();

        return $hbls->map(function ($hbl) {
            return [
                'id' => $hbl->id,
                'hbl_number' => $hbl->hbl_number,
                'hbl_name' => $hbl->hbl_name,
                'email' => $hbl->email,
                'contact_number' => $hbl->contact_number,
                'consignee_name' => $hbl->consignee_name,
                'packages_count' => $hbl->packages->count(),
                'total_weight' => $hbl->packages->sum('weight'),
                'total_volume' => $hbl->packages->sum('volume'),
                'packages' => $hbl->packages->map(function ($package) {
                    return [
                        'id' => $package->id,
                        'package_type' => $package->package_type,
                        'measure_type' => $package->measure_type,
                        'dimensions' => "{$package->length} x {$package->width} x {$package->height}",
                        'quantity' => $package->quantity,
                        'volume' => $package->volume,
                        'weight' => $package->weight,
                        'remarks' => $package->remarks,
                    ];
                }),
            ];
        })->toArray();
    }
}
