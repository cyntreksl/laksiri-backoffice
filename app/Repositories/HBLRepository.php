<?php

namespace App\Repositories;

use App\Actions\HBL\CreateHBL;
use App\Actions\HBL\CreateHBLPackages;
use App\Actions\HBL\DeleteHBL;
use App\Actions\HBL\DownloadHBLBarcodePDF;
use App\Actions\HBL\DownloadHBLInvoicePDF;
use App\Actions\HBL\DownloadHBLPDF;
use App\Actions\HBL\GetHBLByCargoTypeWithDraftLoadedPackages;
use App\Actions\HBL\GetHBLByCargoTypeWithUnloadedPackages;
use App\Actions\HBL\GetHBLs;
use App\Actions\HBL\GetHBLsWithPackages;
use App\Actions\HBL\GetHBLsWithUnloadedPackagesByReference;
use App\Actions\HBL\RestoreHBL;
use App\Actions\HBL\SwitchHoldStatus;
use App\Actions\HBL\UpdateHBL;
use App\Actions\HBL\UpdateHBLPackages;
use App\Actions\HBLDocument\DeleteDocument;
use App\Actions\HBLDocument\UploadDocument;
use App\Factory\HBL\FilterFactory;
use App\Http\Resources\HBLResource;
use App\Interfaces\GridJsInterface;
use App\Interfaces\HBLRepositoryInterface;
use App\Models\Container;
use App\Models\HBL;
use App\Models\HBLDocument;
use App\Models\HBLPackage;
use App\Models\Scopes\BranchScope;
use Illuminate\Http\JsonResponse;

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

    public function getUnloadedHBLsByCargoType(array $data): JsonResponse
    {
        $result = GetHBLByCargoTypeWithUnloadedPackages::run($data);

        return response()->json([
            'data' => $result,
        ]);
    }

    public function getLoadedHBLsByCargoType(Container $container, string $cargoType)
    {
        return GetHBLByCargoTypeWithDraftLoadedPackages::run($container, $cargoType);
    }

    public function getHBLWithUnloadedPackagesByReference(string $reference, string $cargo_type)
    {
        return GetHBLsWithUnloadedPackagesByReference::run($reference, $cargo_type);
    }

    public function downloadHBLPDF(HBL $hbl)
    {
        return DownloadHBLPDF::run($hbl);
    }

    public function downloadHBLInvoicePDF(HBL $hbl)
    {
        return DownloadHBLInvoicePDF::run($hbl);
    }

    public function downloadHBLBarcodePDF(HBL $hbl)
    {
        return DownloadHBLBarcodePDF::run($hbl);
    }

    public function getCancelledList(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = []): JsonResponse
    {
        $query = HBL::query()->onlyTrashed();

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

    public function restore($id)
    {
        return RestoreHBL::run($id);
    }

    public function getHBLByPackageId($package_id): JsonResponse
    {
        $hbl_package = HBLPackage::withoutGlobalScope(BranchScope::class)->where('id', $package_id)->first();

        $hbl = $hbl_package->hbl()->withoutGlobalScope(BranchScope::class)->first();

        return response()->json([
            'data' => $hbl,
        ]);
    }

    public function uploadDocument(array $data): void
    {
        try {
            UploadDocument::run($data);
        } catch (\Exception $e) {
            throw new \Exception('Failed to upload hbl document: '.$e->getMessage());
        }
    }

    public function deleteDocument(HBLDocument $hblDocument)
    {
        try {
            DeleteDocument::run($hblDocument);
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete hbl document: '.$e->getMessage());
        }
    }
}
