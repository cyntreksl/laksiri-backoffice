<?php

namespace App\Repositories\Api;

use App\Actions\Branch\GetBranchById;
use App\Actions\Branch\GetBranchByName;
use App\Actions\BranchPrice\GetPriceRulesByCargoModeAndHBLType;
use App\Actions\HBL\CalculatePayment;
use App\Actions\HBL\CashSettlement\UpdateHBLPayments;
use App\Actions\HBL\CreateHBL;
use App\Actions\HBL\CreateHBLPackages;
use App\Actions\HBL\GetHBLDestinationTotalSummary;
use App\Actions\HBL\GetHBLPackageRules;
use App\Actions\HBL\GetHBLTotalSummary;
use App\Actions\HBL\HBLCharges\UpdateHBLDepartureCharges;
use App\Actions\HBL\HBLCharges\UpdateHBLDestinationCharges;
use App\Actions\HBL\Payments\CreateHBLPayment;
use App\Actions\HBL\UpdateHBLApi;
use App\Actions\HBL\UpdateHBLPackagesApi;
use App\Actions\HBL\Warehouse\GetHBLDestinationTotalConvertedCurrency;
use App\Actions\User\GetUserCurrentBranchID;
use App\Http\Resources\HBLPackageResource;
use App\Http\Resources\HBLResource;
use App\Http\Resources\PickupResource;
use App\Interfaces\Api\HBLRepositoryInterface;
use App\Models\Branch;
use App\Models\HBL;
use App\Traits\ResponseAPI;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lcobucci\JWT\Exception;

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
        $warehouse = GetBranchByName::run($data['warehouse']);
        $data['warehouse_id'] = $warehouse->id;
        //        try {
        $hbl = null;
        DB::transaction(function () use ($data, &$hbl) {
            $hbl = CreateHBL::run($data);
            $packagesData = $data['packages'];
            CreateHBLPackages::run($hbl, $packagesData);

            if (isset($data['paid_amount'])) {
                UpdateHBLPayments::run($data, $hbl);
            }

            // Only create a payment record if paid_amount exists and is greater than 0
            if (! empty($data['paid_amount']) && $data['paid_amount'] > 0) {
                $newPaymentData = [
                    'hbl_id' => $hbl->id,
                    'base_currency_rate_in_lkr' => $hbl->currency_rate,
                    'paid_amount' => $data['paid_amount'],
                    'total_amount' => $data['grand_total'],
                    'due_amount' => $data['grand_total'] - $data['paid_amount'],
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'paid_by' => auth()->id(),
                    'notes' => $data['payment_notes'] ?? 'Initial payment',
                ];
                CreateHBLPayment::run($newPaymentData);
            }

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

        });

        $hbl->addStatus('HBL Preparation by driver');

        return $this->success('HBL created successfully!', $hbl->load('packages'));

        //        } catch (\Exception $e) {
        //            Log::error('Failed to create HBL Repository API: '.$e->getMessage(), [
        //                'data' => $data,
        //                'user_id' => auth()->id(),
        //            ]);
        //            return $this->error($e->getMessage(), $e->getCode());
        //        }
    }

    public function calculatePayment(array $data): JsonResponse
    {
        $destination_branch = Branch::where('name', '=', $data['warehouse'])->get();

        $chargeableWeight = $data['grand_total_weight'];
        if ($data['cargo_type'] == 'Air Cargo') {
            // volumetric weight calculation for Air Cargo
            $volumetricWeight = ($data['grand_total_volume'] * 1000000 / 6000);
            // get higher one as chargeable weight
            $chargeableWeight = max($chargeableWeight, $volumetricWeight);
        }

        $result = CalculatePayment::run(
            $data['cargo_type'],
            $data['hbl_type'],
            $data['grand_total_volume'],
            $chargeableWeight,
            $data['package_list_length'],
            $destination_branch[0]['id'],
            $data['is_active_package'],
            $data['package_list'],
        );

        $currentBranch = GetBranchById::run(GetUserCurrentBranchID::run());
        if ($currentBranch->is_prepaid) {
            $destinationCharge = GetHBLDestinationTotalConvertedCurrency::run($data['cargo_type'], $data['package_list_length'], $data['grand_total_volume'], $chargeableWeight, $destination_branch[0]['id']);
            $result['destination_charges'] = round($destinationCharge['convertedTotalAmountWithTax'], 2);
            $result['sl_rate'] = $destinationCharge['slRate'];
            $result['grand_total_without_discount'] = round(($result['grand_total_without_discount'] + $result['destination_charges']), 2);
        } else {
            $result['destination_charges'] = 0;
        }

        return response()->json($result);
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

    public function getCompletedHBL($data)
    {
        $hbls = HBL::where('system_status', '<', 2.2)
            ->where('created_by', auth()->id())
            ->with(['pickup', 'packages']) // preload needed relations
            ->orderBy('created_at', 'desc')
            ->get();

        $completedPickupsResource = $hbls->map(function ($hbl) {
            if ($hbl->pickup) {
                $hbl->pickup->setRelation('hbl', $hbl);

                return new PickupResource($hbl->pickup);
            } else {
                return [
                    'id' => null,
                    'reference' => $hbl->reference ?? '-',
                    'cargo_type' => $hbl->cargo_type ?? '-',
                    'name' => null,
                    'email' => null,
                    'contact_number' => null,
                    'additional_mobile_number' => null,
                    'whatsapp_number' => null,
                    'address' => null,
                    'location_name' => null,
                    'location_longitude' => null,
                    'location_latitude' => null,
                    'zone' => '-',
                    'driver_assigned_at' => null,
                    'pickup_date' => $hbl->created_at ?? '-',
                    'pickup_time_start' => '-',
                    'pickup_time_end' => '-',
                    'pickup_order' => null,
                    'driver' => '-',
                    'pickup_type' => '-',
                    'pickup_note' => '-',
                    'packages' => $hbl->packages->isNotEmpty() ? HBLPackageResource::collection($hbl->packages) : [],
                    'exception_note' => '-',
                    'hbl' => new HBLResource($hbl),
                    'retry_attempts' => null,
                    'created_by' => $hbl->createdBy->name ?? '-',
                    'hbl_number' => $hbl->hbl_number,
                    'cr_number' => $hbl->cr_number,
                    'status' => $hbl->status ?? '-',
                    'package_types' => \Illuminate\Support\Str::title($hbl->package_types),
                    'driver_generated' => true,
                ];
            }
        })->values();

        return $this->success('Completed pickup list received successfully!', $completedPickupsResource);
    }

    public function completedHBLView(HBL $hbl)
    {
        $hbl->load(['packages', 'pickup', 'pickup.driver', 'pickup.driver.driverLocation', 'pickup.driver.driverLocation.branch']);
        $response = null;

        if ($hbl->pickup) {
            $hbl->pickup->setRelation('hbl', $hbl);

            $response = new PickupResource($hbl->pickup);
        } else {
            $response = [
                'id' => null,
                'reference' => $hbl->reference ?? '-',
                'cargo_type' => $hbl->cargo_type ?? '-',
                'name' => null,
                'email' => null,
                'contact_number' => null,
                'additional_mobile_number' => null,
                'whatsapp_number' => null,
                'address' => null,
                'location_name' => null,
                'location_longitude' => null,
                'location_latitude' => null,
                'zone' => '-',
                'driver_assigned_at' => null,
                'pickup_date' => $hbl->created_at ?? '-',
                'pickup_time_start' => '-',
                'pickup_time_end' => '-',
                'pickup_order' => null,
                'driver' => '-',
                'pickup_type' => '-',
                'pickup_note' => '-',
                'packages' => $hbl->packages->isNotEmpty() ? HBLPackageResource::collection($hbl->packages) : [],
                'exception_note' => '-',
                'hbl' => new HBLResource($hbl),
                'retry_attempts' => null,
                'created_by' => $hbl->createdBy->name ?? '-',
                'hbl_number' => $hbl->hbl_number,
                'cr_number' => $hbl->cr_number,
                'status' => $hbl->status ?? '-',
            ];
        }

        $response['is_destination_charges_paid'] = $hbl->is_destination_charges_paid ?? 0;
        $response['is_departure_charges_paid'] = $hbl->is_departure_charges_paid ?? 0;

        return $this->success('Completed HBL received successfully!', $response);

    }

    public function updateHBL(HBL $hbl, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Capture old payment info before update
            $oldPaidAmount = $hbl->paid_amount ?? 0;
            $oldTotalAmount = $hbl->grand_total ?? 0;

            $hbl = UpdateHBLApi::run($hbl, $data);

            $packagesData = $data['packages'] ?? [];
            UpdateHBLPackagesApi::run($hbl, $packagesData);

            // New payment values from the request
            $newPaidAmount = (float) ($data['paid_amount'] ?? 0);
            $newTotalAmount = (float) ($data['grand_total'] ?? 0);

            // Determine if payment record should be added
            $hasPaidAmountChanged = $newPaidAmount != $oldPaidAmount;
            $hasTotalAmountChanged = $newTotalAmount != $oldTotalAmount;

            if ($hasPaidAmountChanged || $hasTotalAmountChanged) {
                $paymentData = [
                    'hbl_id' => $hbl->id,
                    'base_currency_rate_in_lkr' => $hbl->currency_rate,
                    'paid_amount' => $newPaidAmount,
                    'total_amount' => $newTotalAmount,
                    'due_amount' => $newTotalAmount - $newPaidAmount,
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'paid_by' => auth()->id(),
                    'notes' => $data['payment_notes'] ?? 'Payment was updated because HBL was updated',
                ];

                CreateHBLPayment::run($paymentData);
            }

            DB::commit();

            $hbl->load('packages');

            return $this->success('HBL updated successfully.', [
                'hbl' => $hbl,
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            return $this->error('Failed to update HBL.', ['exception' => $e->getMessage()], 500);
        }
    }

    public function getHBLTotalSummary(HBL $hbl)
    {
        return GetHBLTotalSummary::run($hbl);
    }

    public function getHBLDestinationTotalSummary(HBL $hbl)
    {
        return GetHBLDestinationTotalSummary::run($hbl);
    }
}
