<?php

namespace App\Actions\MHBL;

use App\Actions\HBL\GenerateHBLNumber;
use App\Actions\User\GetUserCurrentBranchID;
use App\Models\MHBL;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateMHBL
{
    use AsAction;

    public function handle(array $data): MHBL
    {
        $reference = GenerateMHBLReferenceNumber::run();
        $data['reference'] = $reference;
        $data['hbl_number'] = GenerateHBLNumber::run(GetUserCurrentBranchID::run());
        $mhbl = MHBL::create($data);

        return $mhbl;
    }
}
