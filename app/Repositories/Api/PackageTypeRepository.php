<?php

namespace App\Repositories\Api;

use App\Actions\PackageType\GetPackageTypes;
use App\Interfaces\Api\PackageTypeRepositoryInterface;
use App\Traits\ResponseAPI;
use Illuminate\Http\JsonResponse;

class PackageTypeRepository implements PackageTypeRepositoryInterface
{
    use ResponseAPI;

    public function getPackageTypes(): JsonResponse
    {
        try {
            return $this->success('Package type list received successfully!', GetPackageTypes::run());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }
}
