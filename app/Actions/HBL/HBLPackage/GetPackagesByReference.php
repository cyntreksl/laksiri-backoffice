<?php

namespace App\Actions\HBL\HBLPackage;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPackagesByReference
{
    use AsAction;

    public function handle(string $reference)
    {
        $hbl = HBL::where('reference', $reference)->firstOrFail();

        $packages = $hbl->packages;

        return response()->json($packages);
    }
}
