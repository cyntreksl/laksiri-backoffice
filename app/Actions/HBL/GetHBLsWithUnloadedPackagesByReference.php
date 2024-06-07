<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLsWithUnloadedPackagesByReference
{
    use AsAction;

    public function handle(string $reference, string $cargo_type)
    {
        $hbl = HBL::where('reference', $reference)
            ->where('cargo_type', $cargo_type)
            ->with(['packages' => function ($query) {
                $query->where('is_loaded', false);
            }])->first();

        if (! $hbl) {
            return response()->json([
                'errors' => [
                    'reference' => ['HBL not found or invalid reference number.'],
                ],
            ], 422);
        } else {
            return response()->json([
                'hbl' => $hbl,
            ]);
        }
    }
}
