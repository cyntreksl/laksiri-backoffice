<?php

namespace App\Actions\HBL\HBLPackage;

use App\Models\HBLPackage;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateHBLPackage
{
    use AsAction;

    public function handle(HBLPackage $hbl_package, array $data): void
    {
        $hbl_package->update($data);
    }
}
