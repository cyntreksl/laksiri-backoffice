<?php

namespace App\Services\PaymentCalculation;

use App\Actions\BranchPrice\GetPriceRulesByCargoModeAndHBLType;
use App\Services\PaymentCalculation\FreightChargeCalculator;
use App\Services\PaymentCalculation\PriceRuleProcessor;
use Illuminate\Database\Eloquent\Collection;

class StandardPaymentCalculator implements PaymentCalculatorInterface
{
    public function __construct(
        private FreightChargeCalculator $freightCalculator,
        private PriceRuleProcessor $priceRuleProcessor
    ) {}

    public function calculate(PaymentCalculationRequest $request): array
    {
        if ($request->isEmpty()) {
            return PaymentCalculationResult::createError('Please add at least one package')->toArray();
        }

        $priceRules = $this->getPriceRules($request);
        $latestPriceRules = $this->priceRuleProcessor->getLatestPriceRules($priceRules);

        if ($latestPriceRules->isEmpty()) {
            return PaymentCalculationResult::createError('Price Rule Not Found!')->toArray();
        }

        $operations = $this->priceRuleProcessor->getSortedOperations($latestPriceRules);
        $validOperations = $this->filterValidOperations($operations, $request->getGrandTotalQuantity());

        if (empty($validOperations)) {
            return PaymentCalculationResult::createError("Please required price rule's condition like > 0")->toArray();
        }

        $freightCalculation = $this->freightCalculator->calculate(
            $validOperations,
            $latestPriceRules,
            $request->getGrandTotalQuantity()
        );

        if (isset($freightCalculation['error'])) {
            return PaymentCalculationResult::createError($freightCalculation['error'])->toArray();
        }

        $billingRule = $latestPriceRules[$validOperations[0]];
        $charges = $this->calculateCharges($request, $billingRule);

        return (new PaymentCalculationResult(
            freightCharge: $freightCalculation['freight_charge'],
            billCharge: $billingRule->bill_price,
            otherCharge: $charges['other_charge'],
            packageCharges: $charges['package_charges'],
            destinationCharges: $charges['destination_charges'],
            isEditable: boolval($billingRule->is_editable),
            vat: $billingRule->bill_vat ? $billingRule->bill_vat / 100 : 0,
            perPackageCharge: floatval($billingRule->per_package_charges),
            perVolumeCharge: floatval($billingRule->volume_charges),
            perFreightCharge: $freightCalculation['per_freight_charge'],
            freightOperator: $freightCalculation['freight_operator'],
            priceMode: $billingRule->price_mode,
            grandTotalWithoutDiscount: $this->calculateGrandTotal($freightCalculation['freight_charge'], $billingRule, $charges),
            freightChargeOperations: $freightCalculation['freight_charge_operations']
        ))->toArray();
    }

    private function getPriceRules(PaymentCalculationRequest $request): Collection
    {
        return GetPriceRulesByCargoModeAndHBLType::run(
            $request->cargoType,
            $request->hblType,
            $request->destinationBranch
        );
    }

    private function filterValidOperations(array $operations, float $grandTotalQuantity): array
    {
        return array_values(array_filter($operations, function ($operation) use ($grandTotalQuantity) {
            $number = floatval(substr($operation, 1));
            return $number < $grandTotalQuantity;
        }));
    }

    private function calculateCharges(PaymentCalculationRequest $request, object $billingRule): array
    {
        if ($request->cargoType === 'Sea Cargo') {
            $destinationCharges = $billingRule->volume_charges * $request->grandTotalVolume;
            $packageCharges = $billingRule->per_package_charges * $request->packageListLength;
        } else {
            $destinationCharges = $billingRule->volume_charges;
            $packageCharges = $billingRule->per_package_charges;
        }

        return [
            'destination_charges' => $destinationCharges,
            'package_charges' => $packageCharges,
            'other_charge' => $destinationCharges + $packageCharges,
        ];
    }

    private function calculateGrandTotal(float $freightCharge, object $billingRule, array $charges): float
    {
        $vat = $billingRule->bill_vat ? $billingRule->bill_vat / 100 : 0;
        return $freightCharge + $billingRule->bill_price + $charges['other_charge'] + $vat;
    }
}
