<?php

namespace App\Actions\HBL;

use App\Actions\BranchPrice\GetPriceRulesByCargoModeAndHBLType;
use App\Actions\PackagePrice\GetPackagePriceRule;
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
        int $destination_branch,
        bool $is_active_package,
        array $package_list,
    ) {
        $freight_charge = 0;
        $bill_charge = 0;
        $destination_charges = 0;
        $package_charges = 0;
        $vat = 0;
        $is_editable = false;

        if ($is_active_package) {
            if (count($package_list) === 0) {
                return [
                    'error' => 'Please add at least one package',
                    'freight_charge' => 0,
                    'bill_charge' => 0,
                    'other_charge' => 0,
                    'package_charges' => 0,
                    'destination_charges' => 0,
                    'is_editable' => 'true',
                    'vat' => $vat,
                    'per_package_charge' => 0.0,
                    'per_volume_charge' => 0.0,
                    'per_freight_charge' => 0.0,
                    'freight_operator' => '',
                    'price_mode' => 'Package',
                    'grand_total_without_discount' => 0.0,
                ];
            }
            foreach ($package_list as $packageItem) {
                $package_Price_Rule = GetPackagePriceRule::run($packageItem['packageRule']);
                $package_charges += ($package_Price_Rule->per_package_charge) * $packageItem['quantity'];
            }
            if ($cargo_type === 'Sea Cargo') {
                $destinationCharges = $package_Price_Rule->volume_charges * $grand_total_volume;
            } else {
                $destinationCharges = $package_Price_Rule->volume_charges;
            }
            $otherCharge = $destinationCharges;
            $vat = $package_Price_Rule->bill_vat ? $package_Price_Rule->bill_vat / 100 : 0;

            return [
                'freight_charge' => 0,
                'bill_charge' => number_format((float) $package_Price_Rule->bill_price, 3, '.', ''),
                'other_charge' => number_format((float) $otherCharge, 3, '.', ''),
                'package_charges' => number_format((float) $package_charges, 3, '.', ''),
                'destination_charges' => number_format((float) $destinationCharges, 3, '.', ''),
                'is_editable' => 'true',
                'vat' => $vat,
                'per_package_charge' => 0.0,
                'per_volume_charge' => 0.0,
                'per_freight_charge' => 0.0,
                'freight_operator' => '',
                'price_mode' => 'Package',
                'grand_total_without_discount' => 0.0,
            ];

        } else {
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

            $grand_total_quantity = $cargo_type === 'Sea Cargo' ? $grand_total_volume : $grand_total_weight;
            if ($grand_total_quantity == 0) {
                return [
                    'error' => 'Please add at least one package',
                    'freight_charge' => $freight_charge,
                    'bill_charge' => $bill_charge,
                    'other_charge' => 0,
                    'package_charges' => $package_charges,
                    'destination_charges' => $destination_charges,
                    'is_editable' => $is_editable,
                    'vat' => $vat,
                ];
            }

            $operations = array_values(array_filter($operations, function ($operation) use ($grand_total_quantity) {
                $number = floatval(substr($operation, 1));

                return $number < $grand_total_quantity;
            }));

            $freight_charge_operations = [];

            foreach ($operations as $operation) {
                $operation_quantity = (float) filter_var($operation, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

                preg_match('/^([*+\-\/]?)(\d+(?:\.\d+)?)/', trim($latestPriceRules[$operation]->true_action), $matches);
                $operator = $matches[1] ?? '';
                $value = floatval($matches[2] ?? 0);
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
                    case '':
                        $freight_charge += $value;
                        break;
                    default:
                        return ['error' => 'Unsupported operation'];
                }
                $freight_charge_operations[] = "{$quantity_after_operation} ".($operator !== '' ? $operator : '=>').' '.number_format((float) $value, 2);
                $grand_total_quantity = $operation_quantity;
            }

            if (empty($operations)) {
                return [
                    'error' => "Please required price rule's condition like > 0 ",
                    'freight_charge' => $freight_charge,
                    'bill_charge' => $bill_charge,
                    'other_charge' => 0,
                    'package_charges' => $package_charges,
                    'destination_charges' => $destination_charges,
                    'is_editable' => $is_editable,
                    'vat' => $vat,
                ];
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

            if ($grand_total_volume === 0.0 && $grand_total_weight === 0.0) {
                return [
                    'freight_charge' => 0.0,
                    'bill_charge' => 0.0,
                    'other_charge' => 0.0,
                    'package_charges' => 0.0,
                    'destination_charges' => 0.0,
                    'is_editable' => $isEditable,
                    'vat' => 0.0,
                    'per_package_charge' => 0.0,
                    'per_volume_charge' => 0.0,
                    'per_freight_charge' => 0.0,
                    'freight_operator' => '',
                    'price_mode' => '',
                    'grand_total_without_discount' => 0.0,
                    'freight_charge_operations' => [],
                ];
            }

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
                'freight_charge_operations' => $freight_charge_operations,
            ];
        }
    }
}
