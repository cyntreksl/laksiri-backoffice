<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLByReference
{
    use AsAction;

    public function handle(string $reference)
    {
        $hbl = HBL::where('reference', $reference)->firstOrFail();

        return response()->json($hbl);
    }
}
