<?php

namespace App\Services\PaymentCalculation;

class PaymentCalculationRequest
{
    public function __construct(
        public readonly string $cargoType,
        public readonly string $hblType,
        public readonly float $grandTotalVolume,
        public readonly float $grandTotalWeight,
        public readonly int $packageListLength,
        public readonly int $destinationBranch,
        public readonly bool $isActivePackage,
        public readonly array $packageList
    ) {}

    public function getGrandTotalQuantity(): float
    {
        return $this->cargoType === 'Sea Cargo' ? $this->grandTotalVolume : $this->grandTotalWeight;
    }

    public function hasPackages(): bool
    {
        return count($this->packageList) > 0;
    }

    public function isEmpty(): bool
    {
        return $this->getGrandTotalQuantity() == 0;
    }
}
