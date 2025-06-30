<?php

namespace App\Services\PaymentCalculation;

class PaymentCalculationResult
{
    public function __construct(
        public readonly float $freightCharge = 0.0,
        public readonly float $billCharge = 0.0,
        public readonly float $otherCharge = 0.0,
        public readonly float $packageCharges = 0.0,
        public readonly float $destinationCharges = 0.0,
        public readonly bool $isEditable = false,
        public readonly float $vat = 0.0,
        public readonly float $perPackageCharge = 0.0,
        public readonly float $perVolumeCharge = 0.0,
        public readonly float $perFreightCharge = 0.0,
        public readonly string $freightOperator = '',
        public readonly string $priceMode = '',
        public readonly float $grandTotalWithoutDiscount = 0.0,
        public readonly array $freightChargeOperations = [],
        public readonly ?string $error = null
    ) {}

    public function toArray(): array
    {
        return [
            'freight_charge' => number_format($this->freightCharge, 3, '.', ''),
            'bill_charge' => number_format($this->billCharge, 3, '.', ''),
            'other_charge' => number_format($this->otherCharge, 2, '.', ''),
            'package_charges' => number_format($this->packageCharges, 2, '.', ''),
            'destination_charges' => number_format($this->destinationCharges, 2, '.', ''),
            'is_editable' => $this->isEditable,
            'vat' => $this->vat,
            'per_package_charge' => $this->perPackageCharge,
            'per_volume_charge' => $this->perVolumeCharge,
            'per_freight_charge' => $this->perFreightCharge,
            'freight_operator' => $this->freightOperator,
            'price_mode' => $this->priceMode,
            'grand_total_without_discount' => number_format($this->grandTotalWithoutDiscount, 2, '.', ''),
            'freight_charge_operations' => $this->freightChargeOperations,
            'error' => $this->error,
        ];
    }

    public static function createError(string $error): self
    {
        return new self(error: $error);
    }

    public static function createEmpty(): self
    {
        return new self(
            isEditable: true,
            priceMode: 'Package'
        );
    }
}
