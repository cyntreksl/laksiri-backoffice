<?php

namespace App\Actions\HBL;

use App\Actions\BranchPrice\GetPriceRulesByCargoModeAndHBLType;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class CalculatePayment
{
    use AsAction;

    public function handle(
        string $cargo_type,
        string $hbl_type,
        float $grand_total_volume,
        float $grand_total_weight,
        int $package_list_length,
        int $destination_branch
    ) {
        $freight_charge = 0;
        $bill_charge = 0;
        $destination_charges = 0;
        $package_charges = 0;
        $vat = 0;
        $is_editable = false;

        $priceRules = GetPriceRulesByCargoModeAndHBLType::run($cargo_type, $hbl_type, $destination_branch);

        $groupedPriceRules = $priceRules->groupBy('condition');
        $latestPriceRules = $groupedPriceRules->map(function (Collection $group) {
            return $group->sortByDesc('updated_at')->first();
        });

        if ($latestPriceRules->isEmpty()) {
            return [
                'error' => 'Price Rule Not Found!',
                'freight_charge' => $freight_charge,
                'bill_charge' => $bill_charge,
                'other_charge' => 0,
                'package_charges' => $package_charges,
                'destination_charges' => $destination_charges,
                'is_editable' => $is_editable,
                'vat' => $vat,
            ];
        }

        $operations = array_keys($latestPriceRules->toArray());
        usort($operations, function ($a, $b) {
            $numA = (int) filter_var($a, FILTER_SANITIZE_NUMBER_INT);
            $numB = (int) filter_var($b, FILTER_SANITIZE_NUMBER_INT);
            return $numB <=> $numA;
        });

        $grand_total_quantity = $cargo_type === "Sea Cargo" ? $grand_total_volume : $grand_total_weight;

        foreach ($operations as $operation) {
            $operation_quantity = (int) filter_var($operation, FILTER_SANITIZE_NUMBER_INT);
            $trueAction = trim($latestPriceRules[$operation]->true_action);
            $operator = $trueAction[0];
            $value = floatval(trim(substr($trueAction, 1)));
            $quantity_after_operation = $grand_total_quantity - $operation_quantity;
            switch ($operator) {
                case '*':
                    $freight_charge += ($quantity_after_operation * $value);
                    break;
                case '+':
                    $freight_charge += ($quantity_after_operation + $value);
                    break;
                case '-':
                    $freight_charge += ($quantity_after_operation - $value);
                    break;
                case '/':
                    if ($value != 0) {
                        $freight_charge += ($quantity_after_operation / $value);
                    } else {
                        return ['error' => 'Division by zero error'];
                    }
                    break;
                default:
                    return ['error' => 'Unsupported operation'];
            }
            $grand_total_quantity = $operation_quantity;
        }

        $billing_rule = $latestPriceRules[$operations[0]];

        $billCharge = $billing_rule->bill_price;
        if ($cargo_type === 'Sea Cargo') {
            $destinationCharges = $billing_rule->volume_charges * $grand_total_volume;
            $packageCharges = $billing_rule->per_package_charges * $package_list_length;
        } else {
            $destinationCharges = $billing_rule->volume_charges;
            $packageCharges = $billing_rule->per_package_charges;
        }
        $isEditable = boolval($billing_rule->is_editable);
        $vat = $billing_rule->bill_vat ? $billing_rule->bill_vat / 100 : 0;
        $otherCharge = $destinationCharges + $packageCharges;
        $grand_total = $freight_charge + $billCharge + $otherCharge + $vat;

        return [
            'freight_charge' => number_format((float) $freight_charge, 3, '.', ''),
            'bill_charge' => number_format((float) $billCharge, 3, '.', ''),
            'other_charge' => number_format((float) $otherCharge, 2, '.', ''),
            'package_charges' => number_format((float) $packageCharges, 2, '.', ''),
            'destination_charges' => number_format((float) $destinationCharges, 2, '.', ''),
            'is_editable' => $isEditable,
            'vat' => $vat,
            'per_package_charge' => floatval($billing_rule->per_package_charges),
            'per_volume_charge' => floatval($billing_rule->volume_charges),
            'per_freight_charge' => (float) $value,
            'freight_operator' => $operator,
            'price_mode' => $billing_rule->price_mode,
            'grand_total_without_discount' => number_format((float) $grand_total, 2, '.', ''),
        ];
    }
}
