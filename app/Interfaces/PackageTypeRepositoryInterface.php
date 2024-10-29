<?php

namespace App\Interfaces;

use App\Models\PackageType;

interface PackageTypeRepositoryInterface
{
    public function getPackageTypes();

    public function storePackageType(array $data);

    public function updatePackageType(array $data, PackageType $packageType);

    public function destroyPackageType(PackageType $packageType);
}
