<?php

namespace App\Repositories;

use App\Actions\Branch\GetBranchByName;
use App\Actions\HBL\GetHBLByHBLNumber;
use App\Actions\MHBL\CreateMHBL;
use App\Actions\MHBL\CreateMHBLsHBL;
use App\Actions\MHBL\DeleteMHBL;
use App\Actions\MHBL\GetContainerLoadedMHBLs;
use App\Actions\MHBL\GetUnloadedMHBLWithHBLsByRef;
use App\Actions\MHBL\GetUnloadMHBLWithHBLs;
use App\Actions\MHBL\UpdateMHBL;
use App\Actions\MHBL\UpdateMHBLsHBL;
use App\Factory\MHBL\FilterFactory;
use App\Http\Resources\MHBLResource;
use App\Interfaces\GridJsInterface;
use App\Interfaces\MHBLRepositoryInterface;
use App\Models\MHBL;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MHBLRepository implements GridJsInterface, MHBLRepositoryInterface
{
    public function storeHBL(array $data)
    {
        $mhbl_data = [
            'branch_id' => session('current_branch_id'),
            'created_by' => Auth::user()->id,
            'consignee_id' => $data['consignee_id'],
            'shipper_id' => $data['shipper_id'],
            'cargo_type' => $data['cargo_type'],
            'grand_volume' => $data['grand_volume'],
            'grand_weight' => $data['grand_weight'],
            'grand_total' => $data['grand_total'],
            'warehouse_id' => GetBranchByName::run($data['warehouse'])->id,
        ];
        $mhbl = CreateMHBL::run($mhbl_data);
        CreateMHBLsHBL::run($mhbl, $data['hbls']);

        return $mhbl;
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = MHBL::with('shipper')->with('consignee');

        if (! empty($search)) {
            $query->whereAny([
                'reference',
                'hbl_number',
            ], 'like', '%'.$search.'%');
        }

        // apply filters
        FilterFactory::apply($query, $filters);

        $mhbls = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => MHBLResource::collection($mhbls),
            'meta' => [
                'total' => $mhbls->total(),
                'current_page' => $mhbls->currentPage(),
                'perPage' => $mhbls->perPage(),
                'lastPage' => $mhbls->lastPage(),
            ],
        ]);
    }

    public function deleteMHBL(MHBL $mhbl)
    {
        return DeleteMHBL::run($mhbl);
    }

    public function addNewHBL(array $data): JsonResponse
    {
        $hbl = GetHBLByHBLNumber::run($data['hbl_number']);

        return response()->json($hbl);
    }

    public function updateMHBL(MHBL $mhbl, array $data)
    {
        $mhbl = UpdateMHBL::run($mhbl, $data);

        UpdateMHBLsHBL::run($mhbl, $data['hbls']);

        return $mhbl;
    }

    public function getUnloadedMHBLs(array $data)
    {
        $result = GetUnloadMHBLWithHBLs::run($data);

        return response()->json([
            'data' => $result,
        ]);
    }

    public function getContainerLoadedMHBLs(array $data)
    {
        $result = GetContainerLoadedMHBLs::run($data);

        return response()->json([
            'data' => $result,
        ]);
    }

    public function getUnloadedMHBLHBL(string $reference)
    {
        $mhbl = GetUnloadedMHBLWithHBLsByRef::run($reference);

        if (! $mhbl) {
            return response()->json([
                'errors' => [
                    'reference' => ['MHBL not found or invalid reference number to load.'],
                ],
            ], 422);
        } else {
            return response()->json([
                'mhbl' => $mhbl,
            ]);
        }
    }
}
