<?php

namespace App\Actions\HBL;

use App\Services\PaymentCalculation\FreightChargeCalculator;
use App\Services\PaymentCalculation\PackagePaymentCalculator;
use App\Services\PaymentCalculation\PaymentCalculationRequest;
use App\Services\PaymentCalculation\PriceRuleProcessor;
use App\Services\PaymentCalculation\StandardPaymentCalculator;
use Lorisleiva\Actions\Concerns\AsAction;

class CalculatePayment
{
    use AsAction;

    public function __construct(
        private PackagePaymentCalculator $packageCalculator,
        private StandardPaymentCalculator $standardCalculator
    ) {}

    public function handle(
        string $cargo_type,
        string $hbl_type,
        float $grand_total_volume,
        float $grand_total_weight,
        int $package_list_length,
        int $destination_branch,
        bool $is_active_package,
        array $package_list,
    ): array {
        $request = new PaymentCalculationRequest(
            cargoType: $cargo_type,
            hblType: $hbl_type,
            grandTotalVolume: $grand_total_volume,
            grandTotalWeight: $grand_total_weight,
            packageListLength: $package_list_length,
            destinationBranch: $destination_branch,
            isActivePackage: $is_active_package,
            packageList: $package_list
        );

        if ($is_active_package) {
            return $this->packageCalculator->calculate($request);
        }

        return $this->standardCalculator->calculate($request);
    }

    public static function make(): self
    {
        $freightCalculator = new FreightChargeCalculator;
        $priceRuleProcessor = new PriceRuleProcessor;
        $packageCalculator = new PackagePaymentCalculator;
        $standardCalculator = new StandardPaymentCalculator($freightCalculator, $priceRuleProcessor);

        return new self($packageCalculator, $standardCalculator);
    }
}
