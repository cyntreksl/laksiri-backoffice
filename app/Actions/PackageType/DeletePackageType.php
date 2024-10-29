<?php

namespace App\Actions\PackageType;

use App\Models\PackageType;
use Lorisleiva\Actions\Concerns\AsAction;

class DeletePackageType
{
    use AsAction;

    public function handle(PackageType $packageType): void
    {
        $packageType->delete();
    }
}
