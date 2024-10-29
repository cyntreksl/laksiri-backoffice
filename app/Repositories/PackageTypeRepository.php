<?php

namespace App\Repositories;

use App\Actions\PackageType\CreatePackageType;
use App\Actions\PackageType\DeletePackageType;
use App\Actions\PackageType\GetPackageTypes;
use App\Actions\PackageType\UpdatePackageType;
use App\Interfaces\PackageTypeRepositoryInterface;
use App\Models\PackageType;

class PackageTypeRepository implements PackageTypeRepositoryInterface
{
    public function getPackageTypes()
    {
        try {
            return GetPackageTypes::run();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get package types: '.$e->getMessage());
        }
    }

    public function storePackageType(array $data)
    {
        try {
            return CreatePackageType::run($data);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create package type: '.$e->getMessage());
        }
    }

    public function updatePackageType(array $data, PackageType $packageType)
    {
        try {
            return UpdatePackageType::run($data, $packageType);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update package type: '.$e->getMessage());
        }
    }

    public function destroyPackageType(PackageType $packageType)
    {
        try {
            return DeletePackageType::run($packageType);
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete package type: '.$e->getMessage());
        }
    }
}
