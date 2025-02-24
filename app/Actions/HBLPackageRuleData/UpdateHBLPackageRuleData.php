<?php

namespace App\Actions\HBLPackageRuleData;

use App\Models\HBLPackage;
use App\Models\HBLPackageRuleData;
use App\Models\Officer;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateHBLPackageRuleData
{
    use AsAction;

    public function handle(HBLPackage $package, array $data)
    {
        $packageRuleData = $package->packageRuleData;

        return $packageRuleData->update($data);
    }
}
