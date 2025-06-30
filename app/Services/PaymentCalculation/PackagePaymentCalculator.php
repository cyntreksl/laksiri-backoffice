<?php

namespace App\Services\PaymentCalculation;

use App\Actions\PackagePrice\GetPackagePriceRule;

class PackagePaymentCalculator implements PaymentCalculatorInterface
{
    public function calculate(PaymentCalculationRequest $request): array
    {
        if (! $request->hasPackages()) {
            return PaymentCalculationResult::createError('Please add at least one package')->toArray();
        }

        $packageCharges = $this->calculatePackageCharges($request->packageList);
        $packagePriceRule = $this->getFirstPackagePriceRule($request->packageList);

        $destinationCharges = $this->calculateDestinationCharges(
            $request->cargoType,
            $request->grandTotalVolume,
            $packagePriceRule->volume_charges
        );

        $vat = $packagePriceRule->bill_vat ? $packagePriceRule->bill_vat / 100 : 0;

        return (new PaymentCalculationResult(
            freightCharge: $packageCharges,
            billCharge: $packagePriceRule->bill_price,
            otherCharge: $destinationCharges,
            destinationCharges: $destinationCharges,
            isEditable: true,
            vat: $vat,
            priceMode: 'Package',
            grandTotalWithoutDiscount: $packageCharges
        ))->toArray();
    }

    private function calculatePackageCharges(array $packageList): float
    {
        $totalCharges = 0.0;

        foreach ($packageList as $packageItem) {
            $packagePriceRule = GetPackagePriceRule::run(
                $packageItem['packageRule'] ?? $packageItem['package_rule']
            );
            $totalCharges += ($packagePriceRule->per_package_charge) * $packageItem['quantity'];
        }

        return $totalCharges;
    }

    private function getFirstPackagePriceRule(array $packageList): object
    {
        $firstPackage = $packageList[0];

        return GetPackagePriceRule::run(
            $firstPackage['packageRule'] ?? $firstPackage['package_rule']
        );
    }

    private function calculateDestinationCharges(string $cargoType, float $totalVolume, float $volumeCharges): float
    {
        if ($cargoType === 'Sea Cargo') {
            return $volumeCharges * $totalVolume;
        }

        return $volumeCharges;
    }
}
