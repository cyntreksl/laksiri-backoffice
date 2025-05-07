<?php

namespace App\Repositories\Api;

use App\Actions\Branch\GetBranchByName;
use App\Actions\BranchPrice\GetPriceRulesByCargoModeAndHBLType;
use App\Actions\HBL\CalculatePayment;
use App\Actions\HBL\CreateHBL;
use App\Actions\HBL\CreateHBLPackages;
use App\Actions\HBL\GetHBLPackageRules;
use App\Http\Resources\HBLPackageResource;
use App\Http\Resources\HBLResource;
use App\Http\Resources\PickupResource;
use App\Interfaces\Api\HBLRepositoryInterface;
use App\Models\Branch;
use App\Models\HBL;
use App\Traits\ResponseAPI;
use Illuminate\Http\JsonResponse;
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
        $warehouse = GetBranchByName::run($data['warehouse']);
        $data['warehouse_id'] = $warehouse->id;
        try {
            $hbl = null;
            DB::transaction(function () use ($data, &$hbl) {
                $hbl = CreateHBL::run($data);
                $packagesData = $data['packages'];
                CreateHBLPackages::run($hbl, $packagesData);
            });

            $hbl->addStatus('HBL Preparation by driver');

            return $this->success('HBL created successfully!', $hbl->load('packages'));

        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function calculatePayment(array $data): JsonResponse
    {
        $destination_branch = Branch::where('name', '=', $data['warehouse'])->get();
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
                    'pickup_date' => $hbl->pickup_date ?? '-',
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
            ];
        }

    }
}
