<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLByHBLNumber
{
    use AsAction;

    public function handle(string $hbl_number)
    {
        $hbl = HBL::where('hbl_number', $hbl_number)->latest()->with('packages')->with('mhbl')->first();

        return $hbl;
    }
}
