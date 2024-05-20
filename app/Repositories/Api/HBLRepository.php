<?php

namespace App\Repositories\Api;

use App\Http\Resources\HBLResource;
use App\Interfaces\Api\HBLRepositoryInterface;
use App\Models\HBL;
use App\Traits\ResponseAPI;
use Illuminate\Http\JsonResponse;
use App\Actions\HBL\CreateHBL;
use App\Actions\HBL\CreateHBLPackages;
use Illuminate\Support\Facades\DB;

class HBLRepository implements HBLRepositoryInterface
{
    use ResponseAPI;

    public function showHBL(HBL $hbl): JsonResponse
    {
        try {
            $hblResource = new HBLResource($hbl);
            return $this->success('Success', $hblResource);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }
    public function storeHBL(array $data): JsonResponse
    {
        try {
            $hbl = null;
            DB::transaction(function () use ($data, &$hbl) {
                $hbl = CreateHBL::run($data);
                $packagesData = $data['packages'];
                CreateHBLPackages::run($hbl, $packagesData);
            });
            return $this->success('HBL created successfully!', $hbl->load('packages'));

        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }
}
