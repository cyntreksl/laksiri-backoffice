<?php

namespace App\Actions\PackageType;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\PackageType;
use Lorisleiva\Actions\Concerns\AsAction;

class CreatePackageType
{
    use AsAction;

    public function handle(array $data): PackageType
    {
        $data['branch_id'] = GetUserCurrentBranchID::run();

        return PackageType::create($data);
    }
}
