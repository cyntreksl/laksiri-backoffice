<?php

namespace App\Services;

use App\Actions\Branch\GetBranchByName;
use App\Actions\BranchPrice\GetPriceRulesByCargoModeAndHBLType;
use App\Actions\PackagePrice\GetPackagePriceRule;
use App\Models\HBL;
use Illuminate\Support\Collection; // Changed from Eloquent Collection

class PriceCalculationService
{
    public function __construct() {}

    public function hblPriceSummary(HBL $hbl): array
    {
        $priceRuleData = $hbl->packages[0]->packageRuleData ?? null;
        $warehouse_id = $hbl['warehouse_id'] ?? GetBranchByName::run($hbl['warehouse'])['id'];
        $data_rules = GetPriceRulesByCargoModeAndHBLType::run($hbl['cargo_type'] ?? '', $hbl['hbl_type'] ?? '', $warehouse_id)->filter(function ($rule) use ($hbl) {
            return $rule['branch_id'] == $hbl['branch_id'];
        });

        if ($priceRuleData && ! $priceRuleData['is_package_rule']) {
            $appliedRules = collect(json_decode($priceRuleData['rules'] ?? '[]', true));
            $totalVolume = $hbl->packages->sum('volume');
            $measuredData = [
                'cargo_type' => $hbl['cargo_type'],
                'grand_total_volume' => $hbl->packages->sum('volume'),
                'grand_total_weight' => $hbl->packages->sum('weight'),
                'package_list_length' => count($hbl->packages),
            ];
            $data = $this->calculateTotalWithPriceRule($appliedRules, $measuredData);
        } elseif (! $priceRuleData && ! $hbl->packages[0]['package_rule']) {
            $measuredData = [
                'cargo_type' => $hbl['cargo_type'],
                'grand_total_volume' => $hbl->packages->sum('volume'),
                'grand_total_weight' => $hbl->packages->sum('weight'),
                'package_list_length' => count($hbl->packages),
            ];
            $data = $this->calculateTotalWithPriceRule($data_rules, $measuredData);
        } elseif ($priceRuleData && $priceRuleData['is_package_rule']) {
            $appliedRules = collect(json_decode($priceRuleData['rules'] ?? '[]', true));
            $measuredData = [
                'cargo_type' => $hbl['cargo_type'],
                'grand_total_volume' => $hbl->packages->sum('volume'),
                'grand_total_weight' => $hbl->packages->sum('weight'),
                'packages' => $hbl->packages,
            ];
            $data = $this->calculateHBLTotalWithPackageRule($measuredData);
        } elseif (! $priceRuleData && $hbl->packages[0]['package_rule']) {
            $measuredData = [
                'cargo_type' => $hbl['cargo_type'],
                'grand_total_volume' => $hbl->packages->sum('volume'),
                'grand_total_weight' => $hbl->packages->sum('weight'),
                'packages' => $hbl->packages,
            ];
            $data = $this->calculateHBLTotalWithPackageRule($measuredData);
        } else {
            $data = [];
        }
        if (count($data) > 0) {
            $data['additional_charge'] = $hbl['additional_charge'] ?? 0;
            $data['discount'] = $hbl['discount'] ?? 0;
        }

        return $data;
    }

    private function calculateHBLTotalWithPackageRule(array $measuredData)
    {
        $freight_charge = 0;
        $bill_charge = 0;
        $destination_charges = 0;
        $package_charges = 0;
        $vat = 0;
        $is_editable = false;
        $other_charge = 0;

        foreach ($measuredData['packages'] as $package) {
            $package_Price_Rule = $package->packageRuleData && $package->packageRuleData['rules'] ? json_decode($package->packageRuleData['rules'], true)[0] : GetPackagePriceRule::run($package['package_rule']);
            $package_charges += ($package_Price_Rule['per_package_charge']) * $package['quantity'];
        }

        if ($measuredData['cargo_type'] === 'Sea Cargo') {
            $destination_charges = $package_Price_Rule['volume_charges'] * $measuredData['grand_total_volume'];
        } else {
            $destination_charges = $package_Price_Rule['volume_charges'];
        }
        $other_charge = $destination_charges;
        $vat = $package_Price_Rule['bill_vat'] ? $package_Price_Rule['bill_vat'] / 100 : 0;

        return [
            'freight_charge' => 0,
            'bill_charge' => number_format((float) $package_Price_Rule['bill_price'], 3, '.', ''),
            'other_charge' => number_format((float) $other_charge, 3, '.', ''),
            'package_charges' => number_format((float) $package_charges, 3, '.', ''),
            'destination_charges' => number_format((float) $destination_charges, 3, '.', ''),
            'is_editable' => 'true',
            'vat' => $vat,
            'per_package_charge' => 0.0,
            'per_volume_charge' => 0.0,
            'per_freight_charge' => 0.0,
            'freight_operator' => '',
            'price_mode' => 'Package',
            'grand_total_without_discount' => number_format((float) $package_charges, 3, '.', ''),
        ];
    }

    private function calculateTotalWithPriceRule(Collection $rules, array $measuredData): array
    {
        if ($rules->count() === 0) {
            return [];
        }
        $groupedPriceRules = $rules->groupBy('condition');
        $latestPriceRules = $groupedPriceRules->map(function (Collection $group) {
            return $group->sortByDesc('updated_at')->first();
        });

        $operations = array_keys($latestPriceRules->toArray());

        usort($operations, function ($a, $b) {
            return ((int) filter_var($b, FILTER_SANITIZE_NUMBER_INT)) <=> ((int) filter_var($a, FILTER_SANITIZE_NUMBER_INT));
        });

        $grand_total_quantity = $measuredData['cargo_type'] === 'Sea Cargo' ? $measuredData['grand_total_volume'] : $measuredData['grand_total_weight'];
        $operations = array_values(array_filter($operations, function ($operation) use ($grand_total_quantity) {
            $number = floatval(substr($operation, 1));

            return $number < $grand_total_quantity;
        }));

        $freight_charge = 0;
        $bill_charge = 0;
        $destination_charges = 0;
        $package_charges = 0;
        $vat = 0;
        $is_editable = false;
        $other_charge = 0;
        $grand_total = 0;

        $freight_charge_operations = [];

        foreach ($operations as $operation) {
            $operation_quantity = (float) filter_var($operation, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $rule = (object) $latestPriceRules[$operation];
            preg_match('/^([*+\-\/]?)(\d+(?:\.\d+)?)/', trim($rule->true_action ?? ''), $matches);
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
        $billing_rule = $latestPriceRules[$operations[0]];
        $bill_charge = $billing_rule['bill_price'];
        if ($measuredData['cargo_type'] === 'Sea Cargo') {
            $destination_charges = $billing_rule['volume_charges'] * $measuredData['grand_total_volume'];
            $package_charges = $billing_rule['per_package_charges'] * $measuredData['package_list_length'];
        } else {
            $destination_charges = $billing_rule['volume_charges'];
            $package_charges = $billing_rule['per_package_charges'];
        }

        $is_editable = boolval($billing_rule['is_editable']);
        $vat = $billing_rule['bill_vat'] ? $billing_rule['bill_vat'] / 100 : 0;
        $other_charge = $destination_charges + $package_charges;
        $grand_total = $freight_charge + $bill_charge + $other_charge + $vat;

        if ($measuredData['grand_total_volume'] === 0.0 && $measuredData['grand_total_weight'] === 0.0) {
            return [
                'freight_charge' => 0.0,
                'bill_charge' => 0.0,
                'other_charge' => 0.0,
                'package_charges' => 0.0,
                'destination_charges' => 0.0,
                'is_editable' => $is_editable,
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
            'bill_charge' => number_format((float) $bill_charge, 3, '.', ''),
            'other_charge' => number_format((float) $other_charge, 2, '.', ''),
            'package_charges' => number_format((float) $package_charges, 2, '.', ''),
            'destination_charges' => number_format((float) $destination_charges, 2, '.', ''),
            'is_editable' => $is_editable,
            'vat' => $vat,
            'per_package_charge' => floatval($billing_rule['per_package_charges']),
            'per_volume_charge' => floatval($billing_rule['volume_charges']),
            'per_freight_charge' => (float) $value,
            'freight_operator' => $operator,
            'price_mode' => $billing_rule['price_mode'],
            'grand_total_without_discount' => number_format((float) $grand_total, 2, '.', ''),
            'freight_charge_operations' => $freight_charge_operations,
        ];
    }
}
