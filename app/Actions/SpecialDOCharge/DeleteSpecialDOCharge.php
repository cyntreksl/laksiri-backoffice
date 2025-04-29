<?php

namespace App\Actions\SpecialDOCharge;

use App\Models\SpecialDOCharge;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteSpecialDOCharge
{
    use AsAction;

    public function handle(SpecialDOCharge $specialDOCharge)
    {
        $specialDOCharge->delete();
    }
}
