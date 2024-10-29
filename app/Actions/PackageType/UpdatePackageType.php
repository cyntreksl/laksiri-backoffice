<?php

namespace App\Actions\PackageType;

use App\Models\PackageType;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdatePackageType
{
    use AsAction;

    public function handle(array $data, PackageType $packageType)
    {
        $packageType->update($data);
    }
}
