<?php

namespace App\Actions\SLInvoice;

use App\Actions\HBL\GenerateHBLNumber;
use App\Actions\User\GetUserCurrentBranchID;
use App\Models\MHBL;
use App\Models\SLInvoice;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateSLInvoice
{
    use AsAction;

    public function handle(array $data): MHBL
    {
        return SLInvoice::create($data);
    }
}
