<?php

namespace App\Repositories;

use App\Actions\HBL\CreateHBL;
use App\Actions\HBL\CreateHBLPackages;
use App\Actions\HBL\DeleteHBL;
use App\Actions\HBL\GetHBLs;
use App\Actions\HBL\GetHBLsWithPackages;
use App\Actions\HBL\SwitchHoldStatus;
use App\Actions\HBL\UpdateHBL;
use App\Actions\HBL\UpdateHBLPackages;
use App\Factory\HBL\FilterFactory;
use App\Http\Resources\HBLResource;
use App\Interfaces\GridJsInterface;
use App\Interfaces\HBLRepositoryInterface;
use App\Models\HBL;

class HBLRepository implements GridJsInterface, HBLRepositoryInterface
{
    public function getHBLs()
    {
        return GetHBLs::run();
    }

    public function storeHBL(array $data)
    {
        $hbl = CreateHBL::run($data);
        $packagesData = $data['packages'];
        CreateHBLPackages::run($hbl, $packagesData);

        return $hbl;
    }

    public function updateHBL(array $data, HBL $hbl)
    {
        $hbl = UpdateHBL::run($hbl, $data);
        $packagesData = $data['packages'];
        UpdateHBLPackages::run($hbl, $packagesData);

        return $hbl;
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = HBL::query();

        if (! empty($search)) {
            $query->whereAny(['reference', 'hbl_name', 'contact_number'], 'like', '%'.$search.'%');
        }

        //apply filters
        FilterFactory::apply($query, $filters);

        $countQuery = $query;

        $hbls = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

        $totalRecords = $countQuery->count();

        return response()->json([
            'data' => HBLResource::collection($hbls),
            'meta' => [
                'total' => $totalRecords,
                'page' => $offset,
                'perPage' => $limit,
                'lastPage' => ceil($totalRecords / $limit),
            ],
        ]);
    }

    public function deleteHBL(HBL $hbl)
    {
        return DeleteHBL::run($hbl);
    }

    public function getHBLsWithPackages()
    {
        return GetHBLsWithPackages::run();
    }

    public function toggleHold(HBL $hbl)
    {
        return SwitchHoldStatus::run($hbl);
    }
}
