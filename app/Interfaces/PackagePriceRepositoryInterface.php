<?php

namespace App\Interfaces;

use App\Models\PackagePrice;

interface PackagePriceRepositoryInterface
{
    public function getPackagePriceRules();

    public function createPackagePriceRule(array $data);

    public function updatePackagePriceRule(PackagePrice $packagePrice, array $data);

    public function deletePackagePriceRule(PackagePrice $packagePrice);
}
