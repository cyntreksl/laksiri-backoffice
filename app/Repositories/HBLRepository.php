<?php

namespace App\Repositories;

use App\Actions\BranchPrice\GetPriceRulesByCargoModeAndHBLType;
use App\Actions\CallFlag\CreateCallFlag;
use App\Actions\Cashier\DownloadGatePassPDF;
use App\Actions\HBL\CalculatePayment;
use App\Actions\HBL\CreateHBL;
use App\Actions\HBL\CreateHBLPackages;
use App\Actions\HBL\DeleteHBL;
use App\Actions\HBL\DownloadHBLBarcodePDF;
use App\Actions\HBL\DownloadHBLInvoicePDF;
use App\Actions\HBL\DownloadHBLPDF;
use App\Actions\HBL\GetHBLByCargoTypeWithDraftLoadedPackages;
use App\Actions\HBL\GetHBLByCargoTypeWithUnloadedPackages;
use App\Actions\HBL\GetHBLByReference;
use App\Actions\HBL\GetHBLPackageRules;
use App\Actions\HBL\GetHBLs;
use App\Actions\HBL\GetHBLStatusByReference;
use App\Actions\HBL\GetHBLsWithPackages;
use App\Actions\HBL\GetHBLsWithUnloadedPackagesByReference;
use App\Actions\HBL\HBLPackage\GetPackagesByReference;
use App\Actions\HBL\RestoreHBL;
use App\Actions\HBL\SwitchHoldStatus;
use App\Actions\HBL\UpdateHBL;
use App\Actions\HBL\UpdateHBLPackages;
use App\Actions\HBLDocument\DeleteDocument;
use App\Actions\HBLDocument\DownloadDocument;
use App\Actions\HBLDocument\UploadDocument;
use App\Exports\CancelledHBLExport;
use App\Exports\HBLExport;
use App\Factory\HBL\FilterFactory;
use App\Http\Resources\HBLResource;
use App\Http\Resources\HBLStatusResource;
use App\Http\Resources\PickupStatusResource;
use App\Interfaces\GridJsInterface;
use App\Interfaces\HBLRepositoryInterface;
use App\Models\Branch;
use App\Models\Container;
use App\Models\HBL;
use App\Models\HBLDocument;
use App\Models\HBLPackage;
use App\Models\PickUp;
use App\Models\Scopes\BranchScope;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;

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

        $hbl->addStatus('HBL Preparation by warehouse');

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
        if (isset($filters['userData'])) {
            $query = HBL::query()
                ->where(function ($query) {
                    $query->where('status', '!=', 'draft')
                        ->orWhereNull('status');
                })->where('hbl_name', $filters['userData'])
                ->orWhere('contact_number', $filters['userData']);
        } else {
            $query = HBL::query()->where(function ($query) {
                $query->where('status', '!=', 'draft')
                    ->orWhereNull('status');
            });
        }

        if (! empty($search)) {
            $query->whereAny([
                'reference',
                'hbl_name',
                'contact_number',
                'consignee_name',
                'consignee_nic',
                'consignee_contact',
                'iq_number',
                'nic',
                'email',
            ], 'like', '%'.$search.'%');
        }

        //apply filters
        FilterFactory::apply($query, $filters);

        $countQuery = $query;
        $totalRecords = $countQuery->count();

        $hbls = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

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
        $totalRecords = $countQuery->count();

        $hbls = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

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

    public function getPickupStatus(HBL $hbl): JsonResponse
    {
        if ($hbl->pickup_id) {
            $pickup = PickUp::find($hbl->pickup_id);

            if ($pickup) {
                return response()->json([
                    'status' => PickupStatusResource::collection($pickup->statusLogs),
                ]);
            }
        }

        return response()->json([
            'status' => [],
        ]);
    }

    public function getHBLStatus(HBL $hbl): JsonResponse
    {
        return response()->json([
            'status' => HBLStatusResource::collection($hbl->statusLogs),
        ]);
    }

    public function export(array $filters)
    {
        return Excel::download(new HBLExport($filters), 'hbls.xlsx');
    }

    public function exportCancelled(array $filters)
    {
        return Excel::download(new CancelledHBLExport($filters), 'hbls-cancelled.xlsx');
    }

    public function getHBLByReference(string $reference): JsonResponse
    {
        return GetHBLByReference::run($reference);
    }

    public function getHBLPackagesByReference(string $reference): JsonResponse
    {
        return GetPackagesByReference::run($reference);
    }

    public function getHBLStatusByReference(string $reference): JsonResponse
    {
        return GetHBLStatusByReference::run($reference);
    }

    public function downloadDocument(HBLDocument $hbl_document)
    {
        return DownloadDocument::run($hbl_document);
    }

    public function calculatePayment(array $data): JsonResponse
    {
        $destination_branch = Branch::where('name', '=', $data['warehouse'])->get();

        if ($destination_branch->count() > 0) {
            $result = CalculatePayment::run(
                $data['cargo_type'],
                $data['hbl_type'],
                $data['grand_total_volume'],
                $data['grand_total_weight'],
                $data['package_list_length'],
                $destination_branch[0]['id'],
                $data['is_active_package'],
                $data['package_list'],
            );

            return response()->json($result);
        }

        return response()->json([]);
    }

    public function getDraftList(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = []): JsonResponse
    {
        if (isset($filters['userData'])) {
            $query = HBL::query()
                ->where('status', 'draft')
                ->where('hbl_name', $filters['userData'])
                ->orWhere('contact_number', $filters['userData']);
        } else {
            $query = HBL::query()
                ->where('status', 'draft');
        }

        if (! empty($search)) {
            $query->whereAny([
                'reference',
                'hbl_name',
                'contact_number',
                'consignee_name',
                'consignee_nic',
                'consignee_contact',
                'iq_number',
                'nic',
                'email',
            ], 'like', '%'.$search.'%');
        }

        $countQuery = $query;
        $totalRecords = $countQuery->count();

        $hbls = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

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

    public function createCallFlag($hbl, array $data): void
    {
        try {
            CreateCallFlag::run($hbl, $data);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create call flag: '.$e->getMessage());
        }
    }

    public function getHBLPackageRules($data): JsonResponse
    {
        try {
            $destination_branch = Branch::where('name', '=', $data['warehouse'])->get();
            $packages = GetHBLPackageRules::run($data['cargo_type'], $data['hbl_type'], $destination_branch[0]['id']);

            return response()->json(['packages' => $packages]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to get package rules '.$e->getMessage());
        }
    }

    public function getHBLRules($data): JsonResponse
    {
        try {
            $destination_branch = Branch::where('name', '=', $data['warehouse'])->get();
            $packagesRules = GetHBLPackageRules::run($data['cargo_type'], $data['hbl_type'], $destination_branch[0]['id']);
            $priceRules = GetPriceRulesByCargoModeAndHBLType::run($data['cargo_type'], $data['hbl_type'], $destination_branch[0]['id']);

            return response()->json(['package_rules' => $packagesRules, 'price_rules' => $priceRules]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to get package rules '.$e->getMessage());
        }
    }

    public function downloadGatePass($hbl, $do_charge = 0)
    {
        return DownloadGatePassPDF::run($hbl, $do_charge);
    }
}
