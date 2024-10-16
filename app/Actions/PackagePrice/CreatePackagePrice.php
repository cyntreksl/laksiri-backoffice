<?php

namespace App\Actions\PackagePrice;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\PackagePrice;
use Lorisleiva\Actions\Concerns\AsAction;

class CreatePackagePrice
{
    use AsAction;

    public function handle(array $data): PackagePrice
    {
        // Get the current branch ID
        $id = GetUserCurrentBranchID::run();

        // Merge the branch ID into the data array
        $data = array_merge($data, ['branch_id' => $id]);

        // Create and return the new PackagePrice
        return PackagePrice::create($data);
    }
}
