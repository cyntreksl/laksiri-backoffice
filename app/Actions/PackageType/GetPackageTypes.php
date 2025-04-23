<?php

namespace App\Actions\PackageType;

use App\Models\PackageType;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPackageTypes
{
    use AsAction;

    public function handle()
    {
        return PackageType::latest()->get();
    }
}
