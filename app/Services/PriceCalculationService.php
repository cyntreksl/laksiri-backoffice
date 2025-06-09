<?php

namespace App\Services;

use App\Actions\Branch\GetBranchByName;
use App\Actions\BranchPrice\GetPriceRulesByCargoModeAndHBLType;
use App\Actions\PackagePrice\GetPackagePriceRule;
use App\Models\HBL;
use Illuminate\Support\Collection;

class PriceCalculationService
{
    public function hblPriceSummary(HBL $hbl): array
    {
        $priceRuleData = $hbl->packages[0]->packageRuleData ?? null;
        $warehouseId = $hbl['warehouse_id'] ?? GetBranchByName::run($hbl['warehouse'])['id'];

        $dataRules = $this->getFilteredPriceRules($hbl, $warehouseId);

        $measuredData = [
            'cargo_type' => $hbl['cargo_type'],
            'grand_total_volume' => $hbl->packages->sum('volume'),
            'grand_total_weight' => $hbl->packages->sum('weight'),
            'package_list_length' => count($hbl->packages),
            'packages' => $hbl->packages,
        ];

        $data = $this->determineCalculationMethod($priceRuleData, $hbl, $measuredData, $dataRules);

        return $this->addAdditionalCharges($data, $hbl);
    }

    private function getFilteredPriceRules(HBL $hbl, $warehouseId): Collection
    {
        return GetPriceRulesByCargoModeAndHBLType::run(
            $hbl['cargo_type'] ?? '',
            $hbl['hbl_type'] ?? '',
            $warehouseId
        )->filter(fn ($rule) => $rule['branch_id'] == $hbl['branch_id']);
    }

    private function determineCalculationMethod(
        ?object $priceRuleData,
        HBL $hbl,
        array $measuredData,
        Collection $dataRules
    ): array {
        if ($priceRuleData && ! $priceRuleData['is_package_rule']) {
            $appliedRules = collect(json_decode($priceRuleData['rules'] ?? '[]', true));

            return $this->calculateTotalWithPriceRule($appliedRules, $measuredData);
        }

        if (! $priceRuleData && ! $hbl->packages[0]['package_rule']) {
            return $this->calculateTotalWithPriceRule($dataRules, $measuredData);
        }

        if (($priceRuleData && $priceRuleData['is_package_rule']) ||
            (! $priceRuleData && $hbl->packages[0]['package_rule'])) {
            return $this->calculateHBLTotalWithPackageRule($measuredData);
        }

        return [];
    }

    private function addAdditionalCharges(array $data, HBL $hbl): array
    {
        if (empty($data)) {
            return $data;
        }

        return array_merge($data, [
            'additional_charge' => $hbl->latestHblPayment->additional_charge ?? 0,
            'discount' => $hbl->latestHblPayment->discount ?? 0,
        ]);
    }

    private function calculateHBLTotalWithPackageRule(array $measuredData): array
    {
        $packageCharges = $this->calculateTotalPackageCharges($measuredData['packages']);

        $packagePriceRule = $this->getPackagePriceRule($measuredData['packages'][0]);

        $destinationCharges = $this->calculateDestinationCharges(
            $measuredData['cargo_type'],
            $measuredData['grand_total_volume'],
            $packagePriceRule['volume_charges']
        );

        return [
            'freight_charge' => 0,
            'bill_charge' => $this->formatNumber($packagePriceRule['bill_price']),
            'other_charge' => $this->formatNumber($destinationCharges),
            'package_charges' => $this->formatNumber($packageCharges),
            'destination_charges' => $this->formatNumber($destinationCharges),
            'is_editable' => 'true',
            'vat' => $packagePriceRule['bill_vat'] ? $packagePriceRule['bill_vat'] / 100 : 0,
            'per_package_charge' => 0.0,
            'per_volume_charge' => 0.0,
            'per_freight_charge' => 0.0,
            'freight_charge_operations' => '',
            'price_mode' => 'Package',
            'grand_total_without_discount' => $this->formatNumber($packageCharges),
        ];
    }

    private function calculateTotalPackageCharges(Collection $packages): float
    {
        return $packages->sum(function ($package) {
            $rule = $this->getPackagePriceRule($package);

            return $rule['per_package_charge'] * $package['quantity'];
        });
    }

    private function getPackagePriceRule($package): array
    {
        return $package->packageRuleData && $package->packageRuleData['rules']
            ? json_decode($package->packageRuleData['rules'], true)[0]
            : GetPackagePriceRule::run($package['package_rule']);
    }

    private function calculateDestinationCharges(string $cargoType, float $totalVolume, float $volumeCharges): float
    {
        return $cargoType === 'Sea Cargo'
            ? $volumeCharges * $totalVolume
            : $volumeCharges;
    }

    private function calculateTotalWithPriceRule(Collection $rules, array $measuredData): array
    {
        if ($rules->isEmpty()) {
            return [];
        }

        $latestPriceRules = $this->getLatestPriceRules($rules);
        $operations = $this->sortAndFilterOperations($latestPriceRules, $measuredData);

        if (empty($operations)) {
            return $this->emptyCalculationResult();
        }

        $calculationResult = $this->processPriceRules(
            $operations,
            $latestPriceRules,
            $measuredData
        );

        return $this->formatCalculationResult($calculationResult, $measuredData);
    }

    private function getLatestPriceRules(Collection $rules): Collection
    {
        return $rules->groupBy('condition')
            ->map(fn (Collection $group) => $group->sortByDesc('updated_at')->first());
    }

    private function sortAndFilterOperations(Collection $latestPriceRules, array $measuredData): array
    {
        $operations = array_keys($latestPriceRules->toArray());

        usort($operations, fn ($a, $b) => ((int) filter_var($b, FILTER_SANITIZE_NUMBER_INT)) <=>
            ((int) filter_var($a, FILTER_SANITIZE_NUMBER_INT))
        );

        $grandTotalQuantity = $measuredData['cargo_type'] === 'Sea Cargo'
            ? $measuredData['grand_total_volume']
            : $measuredData['grand_total_weight'];

        return array_values(array_filter($operations, function ($operation) use ($grandTotalQuantity) {
            $number = floatval(substr($operation, 1));

            return $number < $grandTotalQuantity;
        }));
    }

    private function processPriceRules(array $operations, Collection $latestPriceRules, array $measuredData): array
    {
        $grandTotalQuantity = $measuredData['cargo_type'] === 'Sea Cargo'
            ? $measuredData['grand_total_volume']
            : $measuredData['grand_total_weight'];

        $result = [
            'freight_charge' => 0,
            'freight_charge_operations' => [],
        ];

        foreach ($operations as $operation) {
            $operationQuantity = (float) filter_var($operation, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $rule = (object) $latestPriceRules[$operation];

            $this->applyOperation(
                $result,
                $rule->true_action ?? '',
                $grandTotalQuantity - $operationQuantity
            );

            $this->recordFreightChargeOperation(
                $result,
                $measuredData['cargo_type'],
                $grandTotalQuantity - $operationQuantity,
                $rule->true_action ?? ''
            );

            $grandTotalQuantity = $operationQuantity;
        }

        $result['billing_rule'] = $latestPriceRules[$operations[0]];

        return $result;
    }

    private function applyOperation(array &$result, string $action, float $quantity): void
    {
        preg_match('/^([*+\-\/]?)(\d+(?:\.\d+)?)/', trim($action), $matches);
        $operator = $matches[1] ?? '';
        $value = floatval($matches[2] ?? 0);

        switch ($operator) {
            case '*': $result['freight_charge'] += $quantity * $value;
                break;
            case '+': $result['freight_charge'] += $quantity + $value;
                break;
            case '-': $result['freight_charge'] += $quantity - $value;
                break;
            case '/':
                if ($value != 0) {
                    $result['freight_charge'] += $quantity / $value;
                }
                break;
            case '': $result['freight_charge'] += $value;
                break;
        }
    }

    private function recordFreightChargeOperation(
        array &$result,
        string $cargoType,
        float $quantity,
        string $action
    ): void {
        $measureType = $cargoType === 'Sea Cargo' ? '(Volume) ' : '(Width) ';
        preg_match('/^([*+\-\/]?)(\d+(?:\.\d+)?)/', trim($action), $matches);
        $operator = $matches[1] ?? '';
        $value = $matches[2] ?? 0;

        $result['freight_charge_operations'][] = sprintf(
            '%s %s%s %s',
            $quantity,
            $measureType,
            $operator !== '' ? $operator : '=>',
            number_format((float) $value, 2)
        );
    }

    private function formatCalculationResult(array $calculationResult, array $measuredData): array
    {
        if ($measuredData['grand_total_volume'] === 0.0 && $measuredData['grand_total_weight'] === 0.0) {
            return $this->emptyCalculationResult();
        }

        $billingRule = $calculationResult['billing_rule'];

        $destinationCharges = $measuredData['cargo_type'] === 'Sea Cargo'
            ? $billingRule['volume_charges'] * $measuredData['grand_total_volume']
            : $billingRule['volume_charges'];

        $packageCharges = $measuredData['cargo_type'] === 'Sea Cargo'
            ? $billingRule['per_package_charges'] * $measuredData['package_list_length']
            : $billingRule['per_package_charges'];

        $otherCharge = $destinationCharges + $packageCharges;
        $vat = $billingRule['bill_vat'] ? $billingRule['bill_vat'] / 100 : 0;
        $grandTotal = $calculationResult['freight_charge'] + $billingRule['bill_price'] + $otherCharge + $vat;

        return [
            'freight_charge' => $this->formatNumber($calculationResult['freight_charge']),
            'bill_charge' => $this->formatNumber($billingRule['bill_price']),
            'other_charge' => $this->formatNumber($otherCharge, 2),
            'package_charges' => $this->formatNumber($packageCharges, 2),
            'destination_charges' => $this->formatNumber($destinationCharges, 2),
            'is_editable' => boolval($billingRule['is_editable']),
            'vat' => $vat,
            'per_package_charge' => floatval($billingRule['per_package_charges']),
            'per_volume_charge' => floatval($billingRule['volume_charges']),
            'per_freight_charge' => $this->getPerFreightCharge($calculationResult['billing_rule']),
            'freight_operator' => $this->getFreightOperator($calculationResult['billing_rule']),
            'price_mode' => $billingRule['price_mode'],
            'grand_total_without_discount' => $this->formatNumber($grandTotal, 2),
            'freight_charge_operations' => $calculationResult['freight_charge_operations'],
        ];
    }

    private function emptyCalculationResult(): array
    {
        return [
            'freight_charge' => 0.0,
            'bill_charge' => 0.0,
            'other_charge' => 0.0,
            'package_charges' => 0.0,
            'destination_charges' => 0.0,
            'is_editable' => false,
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

    private function getPerFreightCharge(array $billingRule): float
    {
        preg_match('/^([*+\-\/]?)(\d+(?:\.\d+)?)/', trim($billingRule['true_action'] ?? ''), $matches);

        return (float) ($matches[2] ?? 0);
    }

    private function getFreightOperator(array $billingRule): string
    {
        preg_match('/^([*+\-\/]?)(\d+(?:\.\d+)?)/', trim($billingRule['true_action'] ?? ''), $matches);

        return $matches[1] ?? '';
    }

    private function formatNumber(float $value, int $decimals = 3): string
    {
        return number_format((float) $value, $decimals, '.', '');
    }
}
