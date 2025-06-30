<?php

namespace App\Actions\HBL\HBLCharges;

use App\Actions\AirLine\GetAirLineByName;
use App\Actions\SpecialDOCharge\GetSpecialDOChargeByAgent;
use App\Models\HBL;
use Lorisleiva\Actions\Concerns\AsAction;

class CalculateDoCharge
{
    use AsAction;

    public function handle(HBL $HBL)
    {
        if ($HBL->cargo_mode === 'Sea Cargo') {
            return $this->seaCargoDOCharge($HBL);
        } else {
            return $this->airCargoDOCharge($HBL);
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
                $package_quantity = $hbl->packages
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

        if (! $container || ! $container->airline_name) {
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
        return $hbl->packages[0]->containers()->withoutGlobalScopes()->first() ?? $hbl->packages[0]->duplicate_containers()->withoutGlobalScopes()->first();
    }

}
