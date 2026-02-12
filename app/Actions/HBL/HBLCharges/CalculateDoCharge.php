<?php

namespace App\Actions\HBL\HBLCharges;

use App\Actions\AirLine\GetAirLineByName;
use App\Actions\SpecialDOCharge\GetSpecialDOChargeByAgent;
use App\Models\HBL;
use App\Models\Scopes\BranchScope;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class CalculateDoCharge
{
    use AsAction;

    public function handle(HBL $HBL)
    {
        // Normalize database values to prevent case sensitivity issues
        $cargoType = strtolower(trim($HBL->cargo_type ?? ''));
        $cargoMode = strtolower(trim($HBL->cargo_mode ?? ''));

        if ($cargoType === 'sea cargo' || $cargoMode === 'sea cargo') {
            return $this->seaCargoDOCharge($HBL);
        } else if ($cargoType === 'air cargo' || $cargoMode === 'air cargo') {
            return $this->airCargoDOCharge($HBL);
        } else {
            Log::info('Undefined HBL Cargo Type: ' . $cargoType);
        }
    }

    private function seaCargoDOCharge(HBL $hbl): array
    {
        $doChargeData = [
            'agent_id' => $hbl->branch_id,
            'cargo_type' => $hbl->cargo_type,
            'hbl_type' => $hbl->hbl_type,
        ];
        $groupedRules = collect(GetSpecialDOChargeByAgent::run($doChargeData))->groupBy('package_type');

        // Get packages without BranchScope to avoid filtering issues
        $packages = $this->getPackages($hbl);

        $rate = 0;
        $amount = 0;
        foreach ($groupedRules as $index => $ruleSet) {
            if ($index === 'HBL' && isset($ruleSet[0])) {
                $rate += $ruleSet[0]->charge;
                $amount += $ruleSet[0]->charge;
            } elseif ($index === 'DO' && isset($ruleSet[0])) {
                $rate += $ruleSet[0]->charge;
                $amount += $ruleSet[0]->charge;
            } else {
                $package_quantity = $packages
                    ->where('package_type', $index)
                    ->sum('quantity');
                $groupedDORules = $ruleSet->groupBy('condition');
                $operations = array_keys($groupedDORules->toArray());

                usort($operations, function ($a, $b) {
                    return ((int) filter_var($b, FILTER_SANITIZE_NUMBER_INT)) <=> ((int) filter_var($a, FILTER_SANITIZE_NUMBER_INT));
                });
                $operations = array_values(array_filter($operations, function ($operation) use ($package_quantity) {
                    $number = floatval(substr($operation, 1));

                    return $number < $package_quantity;
                }));
                $packageDOCharge = 0;
                foreach ($operations as $operation) {
                    $operation_quantity = (float) filter_var($operation, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    $rule = (object) $groupedDORules[$operation][0];
                    $quantity_after_operation = $package_quantity - $operation_quantity;
                    $packageDOCharge += ($quantity_after_operation * $rule['charge']);
                    $package_quantity = $operation_quantity;
                }
                $rate += $packageDOCharge;
                $amount += $packageDOCharge;
            }
        }

        return [
            'rate' => $rate,
            'amount' => $amount,
        ];
    }

    private function airCargoDOCharge(HBL $hbl): array
    {
        $container = $this->getContainer($hbl);

        if (empty($container) || ! $container || ! $container->airline_name) {
            return [
                'rate' => 0.00,
                'amount' => 0.00,
            ];
        }

        $airCargoDORule = GetAirLineByName::run($container->airline_name)?->airLineDOCharge;

        if ($airCargoDORule) {
            return [
                'rate' => $airCargoDORule->do_charge,
                'amount' => $airCargoDORule->do_charge,
            ];
        }

        return [
            'rate' => 0.00,
            'amount' => 0.00,
        ];
    }

    private function getContainer($hbl)
    {
        $packages = $this->getPackages($hbl);

        if ($packages->isEmpty()) {
            return null;
        }

        return $packages[0]->containers()->withoutGlobalScopes()->first() ?? $packages[0]->duplicate_containers()->withoutGlobalScopes()->first();
    }

    /**
     * Get packages without BranchScope to avoid filtering issues
     * Always loads packages without scope to ensure they're not filtered
     */
    private function getPackages($hbl)
    {
        // Always load packages without BranchScope to avoid filtering issues
        // Don't rely on pre-loaded packages as they might have been loaded with scope
        return $hbl->packages()->withoutGlobalScope(BranchScope::class)->get();
    }
}
