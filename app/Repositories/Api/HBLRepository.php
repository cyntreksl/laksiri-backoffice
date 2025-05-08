<?php

namespace App\Repositories\Api;

use App\Actions\Branch\GetBranchByName;
use App\Actions\BranchPrice\GetPriceRulesByCargoModeAndHBLType;
use App\Actions\HBL\CalculatePayment;
use App\Actions\HBL\CreateHBL;
use App\Actions\HBL\CreateHBLPackages;
use App\Actions\HBL\GetHBLPackageRules;
use App\Actions\HBL\UpdateHBLApi;
use App\Actions\HBL\UpdateHBLPackages;
use App\Actions\HBL\UpdateHBLPackagesApi;
use App\Http\Resources\HBLResource;
use App\Interfaces\Api\HBLRepositoryInterface;
use App\Models\Branch;
use App\Models\HBL;
use App\Traits\ResponseAPI;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
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

    public function updateHBL(HBL $hbl, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $hbl = UpdateHBLApi::run($hbl, $data);

            $packagesData = $data['packages'] ?? [];
            UpdateHBLPackagesApi::run($hbl, $packagesData);

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
}
