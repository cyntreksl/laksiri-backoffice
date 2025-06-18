<?php

namespace App\Actions\HBL\Warehouse;

use App\Actions\Branch\GetBranchById;
use App\Actions\User\GetUserCurrentBranchID;
use App\Models\Currency;
use App\Models\Tax;
use App\Services\GatePassChargesService;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLDestinationTotalConvertedCurrency
{
    use AsAction;

    public function handle($cargoType, int $packageCount, float $grandVolume, float $grandWeight = 0.0)
    {
        //        try {
        $service = new GatePassChargesService($cargoType);

        $handlingCharges = $this->calculateHandlingCharges($service, $packageCount);
        $slpaCharge = $this->calculateSlpaCharge($service, $grandVolume);
        $bondCharge = $this->calculateBondCharge($service, $grandVolume, $grandWeight);

        $totalAmount = $handlingCharges + $slpaCharge + $bondCharge;

        $taxes = Tax::whereIsActive(true)
            ->get();

        $totalTax = 0.0;
        foreach ($taxes as $tax) {
            $taxAmount = ($totalAmount * $tax->rate) / 100;
            $totalTax += $taxAmount;
        }

        $totalWithTax = $totalAmount + $totalTax;

        $branch = GetBranchById::run(GetUserCurrentBranchID::run());
        $currencyRate = Currency::whereCurrencySymbol($branch->currency_symbol)
            ->latest()
            ->first();

        $slRate = 1;
        $currencySymbol = $branch->currency_symbol;

        $convertedHandlingCharges = $handlingCharges;
        $convertedSlpaCharge = $slpaCharge;
        $convertedBondCharge = $bondCharge;
        $convertedTotalAmount = $totalAmount;
        $convertedTotalTax = $totalTax;
        $convertedTotalAmountWithTax = $totalWithTax;

        if ($currencyRate instanceof Currency) {
            $rate = $currencyRate->sl_rate;
            $slRate = 1 / $rate;
            $currencySymbol = $currencyRate->currency_symbol;

            $convertedHandlingCharges = $handlingCharges * $slRate;
            $convertedSlpaCharge = $slpaCharge * $slRate;
            $convertedBondCharge = $bondCharge * $slRate;
            $convertedTotalAmount = $totalAmount * $slRate;
            $convertedTotalTax = $totalTax * $slRate;
            $convertedTotalAmountWithTax = $totalWithTax * $slRate;

        }

        return [
            'handlingCharges' => $handlingCharges,
            'slpaCharge' => $slpaCharge,
            'bondCharge' => $bondCharge,
            'dOCharge' => 0,
            'totalAmount' => $totalAmount,
            'totalTax' => $totalTax,
            'totalAmountWithTax' => $totalWithTax,
            'slRate' => $slRate,
            'currencySymbol' => $currencySymbol,
            'convertedHandlingCharges' => $convertedHandlingCharges,
            'convertedSlpaCharge' => $convertedSlpaCharge,
            'convertedBondCharge' => $convertedBondCharge,
            'convertedTotalAmount' => $convertedTotalAmount,
            'convertedTotalTax' => $convertedTotalTax,
            'convertedTotalAmountWithTax' => $convertedTotalAmountWithTax,
        ];

        //        } catch (\Exception $e) {
        //            Log::error('Error calculating HBL destination total summary: '.$e->getMessage(), [
        //                'exception' => $e,
        //            ]);
        //
        //            return $this->getDefaultResponse();
        //        }
    }

    private function calculateHandlingCharges(GatePassChargesService $service, int $packageCount): float
    {
        try {

            return $service->handlingCharge($packageCount)['amount'] ?? 0.0;
        } catch (\Exception $e) {
            Log::warning('Error calculating handling charges: '.$e->getMessage());

            return 0.0;
        }
    }

    private function calculateSlpaCharge(GatePassChargesService $service, float $grandVolume): float
    {
        try {
            return $service->portCharge($grandVolume)['amount'] ?? 0.0;
        } catch (\Exception $e) {
            Log::warning('Error calculating SLPA charge: '.$e->getMessage());

            return 0.0;
        }
    }

    private function calculateBondCharge(GatePassChargesService $service, float $totalVolume, float $totalWeight): float
    {
        try {
            return $service->bondCharge($totalVolume, $totalWeight)['amount'] ?? 0.0;
        } catch (\Exception $e) {
            Log::warning('Error calculating bond charge: '.$e->getMessage());

            return 0.0;
        }
    }

    private function getDefaultResponse(): array
    {
        return [
            'handlingCharges' => 0.0,
            'slpaCharge' => 0.0,
            'bondCharge' => 0.0,
            'demurrageCharge' => 0.0,
            'totalAmount' => 0.0,
            'totalTax' => 0.0,
            'totalAmountWithTax' => 0.0,
            'slRate' => 0.0,
            'currencySymbol' => 0.0,
            'convertedHandlingCharges' => 0.0,
            'convertedSlpaCharge' => 0.0,
            'convertedBondCharge' => 0.0,
            'convertedTotalAmount' => 0.0,
            'convertedTotalTax' => 0.0,
            'convertedTotalAmountWithTax' => 0.0,
        ];
    }
}
