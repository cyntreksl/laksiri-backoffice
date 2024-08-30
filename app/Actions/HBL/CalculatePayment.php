<?php

namespace App\Actions\HBL;

use App\Actions\BranchPrice\GetPriceRulesByCargoModeAndHBLType;
use Lorisleiva\Actions\Concerns\AsAction;

class CalculatePayment
{
    use AsAction;

    public function handle(
        string $cargo_type,
        string $hbl_type,
        float $grand_total_volume,
        float $grand_total_weight,
        int $package_list_length
    ) {
        $freight_charge = 0;
        $bill_charge = 0;
        $destination_charges = 0;
        $package_charges = 0;
        $vat = 0;
        $is_editable = false;

        $priceRule = GetPriceRulesByCargoModeAndHBLType::run($cargo_type, $hbl_type);

        if (! $priceRule) {
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

        if ($priceRule->price_mode === 'volume') {
            $trueAction = trim($priceRule->true_action);
            $operator = $trueAction[0];
            $value = floatval(trim(substr($trueAction, 1)));
            switch ($operator) {
                case '*':
                    $freight_charge = $grand_total_volume * $value;
                    break;
                case '+':
                    $freight_charge = $grand_total_volume + $value;
                    break;
                case '-':
                    $freight_charge = $grand_total_volume - $value;
                    break;
                case '/':
                    if ($value != 0) {
                        $freight_charge = $grand_total_volume / $value;
                    } else {
                        return ['error' => 'Division by zero error'];
                    }
                    break;
                default:
                    return ['error' => 'Unsupported operation'];
            }
        } elseif ($priceRule->price_mode === 'weight') {
            $trueAction = trim($priceRule->true_action);
            $operator = $trueAction[0];
            $value = floatval(trim(substr($trueAction, 1)));

            switch ($operator) {
                case '*':
                    $freight_charge = $grand_total_weight * $value;
                    break;
                case '+':
                    $freight_charge = $grand_total_weight + $value;
                    break;
                case '-':
                    $freight_charge = $grand_total_weight - $value;
                    break;
                case '/':
                    if ($value != 0) {
                        $freight_charge = $grand_total_weight / $value;
                    } else {
                        return ['error' => 'Division by zero error'];
                    }
                    break;
                default:
                    return ['error' => 'Unsupported operation'];
            }
        }

        $billCharge = $priceRule->bill_price;
        if ($grand_total_volume) {
            $destinationCharges = $priceRule->volume_charges * $grand_total_volume;
            $packageCharges = $priceRule->per_package_charges * $package_list_length;
        } else {
            $destinationCharges = $priceRule->volume_charges;
            $packageCharges = $priceRule->per_package_charges;
        }
        $isEditable = boolval($priceRule->is_editable);
        $vat = $priceRule->bill_vat ? $priceRule->bill_vat / 100 : 0;
        $otherCharge = $destinationCharges + $packageCharges;

        return [
            'freight_charge' => number_format((float) $freight_charge, 3, '.', ''),
            'bill_charge' => number_format((float) $billCharge, 3, '.', ''),
            'other_charge' => number_format((float) $otherCharge, 2, '.', ''),
            'package_charges' => number_format((float) $packageCharges, 2, '.', ''),
            'destination_charges' => number_format((float) $destinationCharges, 2, '.', ''),
            'is_editable' => $isEditable,
            'vat' => $vat,
            'per_package_charge' => floatval($priceRule->per_package_charges),
            'per_volume_charge' => floatval($priceRule->volume_charges),
            'per_freight_charge' => (float) $value,
            'freight_operator' => $operator,
            'price_mode' => $priceRule->price_mode,
        ];
    }
}
