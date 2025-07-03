<?php

namespace App\Repositories;

use App\Actions\Branch\GetBranchById;
use App\Actions\BranchPrice\GetPriceRulesByCargoModeAndHBLType;
use App\Actions\CallFlag\CreateCallFlag;
use App\Actions\Cashier\DownloadCashierInvoicePDF;
use App\Actions\Examination\DownloadGatePassPDF;
use App\Actions\HBL\CalculatePayment;
use App\Actions\HBL\CashSettlement\UpdateHBLPayments;
use App\Actions\HBL\CreateHBL;
use App\Actions\HBL\CreateHBLPackages;
use App\Actions\HBL\DeleteHBL;
use App\Actions\HBL\DownloadBaggagePDF;
use App\Actions\HBL\DownloadHBLBarcodePDF;
use App\Actions\HBL\DownloadHBLInvoicePDF;
use App\Actions\HBL\DownloadHBLPDF;
use App\Actions\HBL\GetHBLByCargoTypeWithDestinationUnloadedPackages;
use App\Actions\HBL\GetHBLByCargoTypeWithDraftLoadedPackages;
use App\Actions\HBL\GetHBLByCargoTypeWithUnloadedPackages;
use App\Actions\HBL\GetHBLByReference;
use App\Actions\HBL\GetHBLDestinationTotalSummary;
use App\Actions\HBL\GetHBLPackageRules;
use App\Actions\HBL\GetHBLs;
use App\Actions\HBL\GetHBLStatusByReference;
use App\Actions\HBL\GetHBLsWithPackages;
use App\Actions\HBL\GetHBLsWithUnloadedPackagesByReference;
use App\Actions\HBL\GetHBLTotalSummary;
use App\Actions\HBL\HBLCharges\UpdateHBLDepartureCharges;
use App\Actions\HBL\HBLCharges\UpdateHBLDestinationCharges;
use App\Actions\HBL\HBLPackage\GetPackagesByReference;
use App\Actions\HBL\MarkAsRTF;
use App\Actions\HBL\MarkAsUnRTF;
use App\Actions\HBL\Payments\CreateHBLPayment;
use App\Actions\HBL\RestoreHBL;
use App\Actions\HBL\SwitchHoldStatus;
use App\Actions\HBL\UpdateHBL;
use App\Actions\HBL\UpdateHBLPackages;
use App\Actions\HBL\Warehouse\GetHBLDestinationTotalConvertedCurrency;
use App\Actions\HBLDocument\DeleteDocument;
use App\Actions\HBLDocument\DownloadDocument;
use App\Actions\HBLDocument\UploadDocument;
use App\Actions\MHBL\DeleteMHBLsHBL;
use App\Actions\User\GetUserCurrentBranchID;
use App\Enum\HBLType;
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
use App\Models\CustomerQueue;
use App\Models\HBL;
use App\Models\HBLDocument;
use App\Models\HBLPackage;
use App\Models\PickUp;
use App\Models\Scopes\BranchScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
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

        $hbl->refresh();

        if (isset($data['paid_amount'])) {
            UpdateHBLPayments::run($data, $hbl);
        }

        // Payment creation
        $newPaymentData = [
            'hbl_id' => $hbl->id,
            'paid_amount' => $data['paid_amount'],
            'total_amount' => $data['grand_total'],
            'due_amount' => $data['grand_total'] - $data['paid_amount'],
            'payment_method' => $data['payment_method'] ?? null,
            'paid_by' => auth()->id(),
            'notes' => $data['payment_notes'] ?? null,
        ];
        CreateHBLPayment::run($newPaymentData);

        $paymentData = [
            'freight_charge' => $data['freight_charge'],
            'bill_charge' => $data['bill_charge'],
            'other_charge' => $data['other_charge'],
            'destination_charge' => $data['destination_charge'],
            'package_charges' => $data['package_charges'],
            'discount' => $data['discount'],
            'additional_charge' => $data['additional_charge'],
            'grand_total' => $data['grand_total'],
            'paid_amount' => $data['paid_amount'],
            'is_departure_charges_paid' => $data['is_departure_charges_paid'],
            'is_destination_charges_paid' => $data['is_destination_charges_paid'],
        ];

        UpdateHBLDepartureCharges::run($hbl, $paymentData);
        UpdateHBLDestinationCharges::run($hbl, $paymentData);

        return $hbl;
    }

    public function updateHBL(array $data, HBL $hbl)
    {
        DB::transaction(function () use (&$hbl, $data) {
            // Capture previous amounts before update
            $oldPaidAmount = $hbl->paid_amount ?? 0;
            $oldTotalAmount = $hbl->grand_total ?? 0;

            $hbl = UpdateHBL::run($hbl, $data);
            $packagesData = $data['packages'];
            UpdateHBLPackages::run($hbl, $packagesData);

            // Use updated values from the request
            $newPaidAmount = (float) ($data['paid_amount'] ?? 0);
            $newTotalAmount = (float) ($data['grand_total'] ?? 0);

            $hasPaidAmountChanged = $newPaidAmount != $oldPaidAmount;
            $hasTotalAmountChanged = $newTotalAmount != $oldTotalAmount;

            if ($hasPaidAmountChanged || $hasTotalAmountChanged) {
                $newPaymentData = [
                    'hbl_id' => $hbl->id,
                    'paid_amount' => $newPaidAmount,
                    'total_amount' => $newTotalAmount,
                    'due_amount' => $newTotalAmount - $newPaidAmount,
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'paid_by' => auth()->id(),
                    'notes' => $data['payment_notes'] ?? null,
                ];

                CreateHBLPayment::run($newPaymentData);
            }

            $hbl->refresh();
        });

        return $hbl;
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = []): JsonResponse
    {
        if (isset($filters['userData'])) {
            $query = HBL::query()
                ->with('mhbl')
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
                'hbl_number',
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

        // apply filters
        FilterFactory::apply($query, $filters);

        $hbls = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => HBLResource::collection($hbls),
            'meta' => [
                'total' => $hbls->total(),
                'current_page' => $hbls->currentPage(),
                'perPage' => $hbls->perPage(),
                'lastPage' => $hbls->lastPage(),
            ],
        ]);
    }

    public function deleteHBL(HBL $hbl)
    {
        DeleteMHBLsHBL::run($hbl);

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

    public function getDestinationUnloadedHBLsByCargoType(array $data): JsonResponse
    {
        $result = GetHBLByCargoTypeWithDestinationUnloadedPackages::run($data);

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

        // apply filters
        FilterFactory::apply($query, $filters);

        $hbls = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => HBLResource::collection($hbls),
            'meta' => [
                'total' => $hbls->total(),
                'current_page' => $hbls->currentPage(),
                'perPage' => $hbls->perPage(),
                'lastPage' => $hbls->lastPage(),
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

    public function getPickupStatus($id): JsonResponse
    {
        if ($id) {
            $pickup = PickUp::find($id);

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

            $currentBranch = GetBranchById::run(GetUserCurrentBranchID::run());
            if ($currentBranch->is_prepaid) {
                $destinationCharge = GetHBLDestinationTotalConvertedCurrency::run($data['cargo_type'], $data['package_list_length'], $data['grand_total_volume'], $data['grand_total_weight'], $destination_branch[0]['id']);
                $result['destination_charges'] = round($destinationCharge['convertedTotalAmountWithTax'], 2);
                $result['sl_rate'] = $destinationCharge['slRate'];
            } else {
                $result['destination_charges'] = 0;
            }

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

        FilterFactory::apply($query, $filters);

        $hbls = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => HBLResource::collection($hbls),
            'meta' => [
                'total' => $hbls->total(),
                'current_page' => $hbls->currentPage(),
                'perPage' => $hbls->perPage(),
                'lastPage' => $hbls->lastPage(),
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
            $packagesRules = GetHBLPackageRules::run($data['cargo_type'] ?? '', $data['hbl_type'] ?? '', $destination_branch[0]['id'] ?? 0);
            $priceRules = GetPriceRulesByCargoModeAndHBLType::run($data['cargo_type'] ?? '', $data['hbl_type'] ?? '', $destination_branch[0]['id'] ?? 0);

            return response()->json(['package_rules' => $packagesRules, 'price_rules' => $priceRules]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to get package rules '.$e->getMessage());
        }
    }

    public function downloadCashierInvoice($hbl)
    {
        try {
            return DownloadCashierInvoicePDF::run($hbl);
        } catch (\Exception $e) {
            throw new \Exception('Failed to download cashier invoice'.$e->getMessage());
        }
    }

    public function getDoorToDoorHBL(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        if (isset($filters['userData'])) {
            $query = HBL::query()
                ->where('hbl_type', '=', HBLType::DOOR_TO_DOOR->value)
                ->where(function ($query) {
                    $query->where('status', '!=', 'draft')
                        ->orWhereNull('status');
                })->where('hbl_name', $filters['userData'])
                ->orWhere('contact_number', $filters['userData']);
        } else {
            $query = HBL::query()->where('hbl_type', '=', HBLType::DOOR_TO_DOOR->value)->where(function ($query) {
                $query->where('status', '!=', 'draft')
                    ->orWhereNull('status');
            });
        }

        if (! empty($search)) {
            $query->whereAny([
                'reference',
                'hbl_number',
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

        // apply filters
        FilterFactory::apply($query, $filters);

        $hbls = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => HBLResource::collection($hbls),
            'meta' => [
                'total' => $hbls->total(),
                'current_page' => $hbls->currentPage(),
                'perPage' => $hbls->perPage(),
                'lastPage' => $hbls->lastPage(),
            ],
        ]);
    }

    public function downloadBaggagePDF(HBL $hbl)
    {
        return DownloadBaggagePDF::run($hbl);
    }

    public function getHBLTotalSummary(HBL $hbl)
    {
        return GetHBLTotalSummary::run($hbl);
    }

    public function getHBLsPackages(array $data)
    {
        $ids = collect($data)->flatten()->toArray();
        $hbls = HBL::whereIn('id', $ids)->with('mhbl')->get();
        $groupedPackagesArray = $hbls->flatMap(function ($hbl) {
            $groupKey = $hbl->id;

            return $hbl->packages->map(function ($package) use ($groupKey) {
                return ['group_key' => $groupKey, 'package' => $package];
            });
        })->groupBy('group_key')->map(function ($group) {
            return $group->pluck('package');
        })->map(function ($packages) {
            return $packages->toArray();
        })->toArray();

        return response()->json([
            'hblsPackages' => $groupedPackagesArray,
        ]);
    }

    public function getHBLDestinationTotalSummary(HBL $hbl)
    {
        return GetHBLDestinationTotalSummary::run($hbl);
    }

    public function downloadGatePass($hbl, CustomerQueue $customerQueue)
    {
        try {
            return DownloadGatePassPDF::run($hbl, $customerQueue);
        } catch (\Exception $e) {
            throw new \Exception('Failed to download gate pass '.$e->getMessage());
        }
    }

    public function doRTF(HBL $hbl): void
    {
        try {
            MarkAsRTF::run($hbl);

            $packages = $hbl->packages;

            foreach ($packages as $package) {
                \App\Actions\HBL\HBLPackage\MarkAsRTF::run($package);
            }

        } catch (\Exception $e) {
            throw new \Exception('Failed to mark as rtf HBL: '.$e->getMessage());
        }
    }

    public function undoRTF(HBL $hbl): void
    {
        try {
            MarkAsUnRTF::run($hbl);

            $packages = $hbl->packages;

            foreach ($packages as $package) {
                \App\Actions\HBL\HBLPackage\MarkAsUnRTF::run($package);
            }

        } catch (\Exception $e) {
            throw new \Exception('Failed to undo rtf HBL: '.$e->getMessage());
        }
    }

    public function doPackageRTF(HBLPackage $hbl_package): void
    {
        try {
            \App\Actions\HBL\HBLPackage\MarkAsRTF::run($hbl_package);
        } catch (\Exception $e) {
            throw new \Exception('Failed to mark as rtf HBL Package: '.$e->getMessage());
        }
    }

    public function undoPackageRTF(HBLPackage $hbl_package): void
    {
        try {
            \App\Actions\HBL\HBLPackage\MarkAsUnRTF::run($hbl_package);
        } catch (\Exception $e) {
            throw new \Exception('Failed to undo rtf HBL Package: '.$e->getMessage());
        }
    }
}
