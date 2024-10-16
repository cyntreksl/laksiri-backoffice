<?php

namespace App\Repositories;

use App\Actions\PackagePrice\CreatePackagePrice;
use App\Actions\PackagePrice\DeletePackagePriceRule;
use App\Actions\PackagePrice\GetPackagePriceRules;
use App\Actions\PackagePrice\UpdatePackagePriceRule;
use App\Interfaces\PackagePriceRepositoryInterface;
use App\Models\PackagePrice;

class PackagePriceRepository implements PackagePriceRepositoryInterface
{

    public function getPackagePriceRules()
    {
        return GetPackagePriceRules::run();
    }
    public function createPackagePriceRule(array $data)
    {
        return CreatePackagePrice::run($data);
    }

    public function updatePackagePriceRule(PackagePrice $packagePrice, array $data)
    {
        return UpdatePackagePriceRule::run($packagePrice, $data);
    }

    public function deletePackagePriceRule(PackagePrice $packagePrice)
    {
        return DeletePackagePriceRule::run($packagePrice);
    }
}
