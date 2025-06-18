<?php

namespace App\Actions\HBL;

use App\Models\Tax;
use App\Services\GatePassChargesService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class GetHBLDestinationTotalSummary
{
    use AsAction;

    public function handle($hbl)
    {
        try {
            if (! $hbl) {
                throw new \InvalidArgumentException('HBL data cannot be null');
            }

            $service = new GatePassChargesService($hbl['cargo_type'] ?? null);

            $container = $this->getContainer($hbl);
            $arrivalDatesCount = $this->calculateArrivalDatesCount($container);

            $handlingCharges = $this->calculateHandlingCharges($service, $hbl);
            $slpaCharge = $this->calculateSlpaCharge($service, $hbl);
            $bondCharge = $this->calculateBondCharge($service, $hbl);
            $demurrageCharge = $this->calculateDemurrageCharge($service, $hbl, $container, $arrivalDatesCount);
            $dOCharge = $this->getDOCharge($service, $hbl);

            $totalAmount = $handlingCharges + $slpaCharge + $bondCharge + $demurrageCharge + $dOCharge;

            $taxes = Tax::whereIsActive(true)
                ->get();

            $totalTax = 0.0;
            foreach ($taxes as $tax) {
                $taxAmount = ($totalAmount * $tax->rate) / 100;
                $totalTax += $taxAmount;
            }

            $totalWithTax = $totalAmount + $totalTax;

            return [
                'handlingCharges' => $handlingCharges,
                'slpaCharge' => $slpaCharge,
                'bondCharge' => $bondCharge,
                'demurrageCharge' => $demurrageCharge,
                'dOCharge' => $dOCharge,
                'totalAmount' => $totalAmount,
                'totalTax' => $totalTax,
                'totalAmountWithTax' => $totalWithTax,
            ];

        } catch (\Exception $e) {
            Log::error('Error calculating HBL destination total summary: '.$e->getMessage(), [
                'hbl_id' => $hbl->id ?? null,
                'exception' => $e,
            ]);

            return $this->getDefaultResponse();
        }
    }

    private function getContainer($hbl)
    {
        try {
            if (! isset($hbl->packages) || $hbl->packages->isEmpty()) {
                return null;
            }

            $firstPackage = $hbl->packages->first();

            return $firstPackage->containers()->withoutGlobalScopes()->first()
                ?? $firstPackage->duplicate_containers()->withoutGlobalScopes()->first();

        } catch (\Exception $e) {
            Log::warning('Error getting container for HBL: '.$e->getMessage());

            return null;
        }
    }

    private function calculateArrivalDatesCount($container): int
    {
        if (! $container || ! isset($container['estimated_time_of_arrival'])) {
            return 0;
        }

        try {
            $eta = Carbon::parse($container['estimated_time_of_arrival']);

            return $eta->diffInDays(Carbon::now()->startOfDay(), false);
        } catch (\Exception $e) {
            Log::warning('Error calculating arrival dates count: '.$e->getMessage());

            return 0;
        }
    }

    private function getVatCharge(GatePassChargesService $service, $hbl): float
    {
        try {
            return $service->vatCharge($hbl)['rate'] ?? 0.0;
        } catch (\Exception $e) {
            Log::warning('Error getting VAT charge: '.$e->getMessage());

            return 0.0;
        }
    }

    private function calculateHandlingCharges(GatePassChargesService $service, $hbl, float $vat = 0): float
    {
        try {
            $packageCount = $hbl->packages->count();

            return $service->handlingCharge($packageCount)['amount'] ?? 0.0;
        } catch (\Exception $e) {
            Log::warning('Error calculating handling charges: '.$e->getMessage());

            return 0.0;
        }
    }

    private function calculateSlpaCharge(GatePassChargesService $service, $hbl): float
    {
        try {
            $totalVolume = $hbl->packages->sum('volume');

            return $service->portCharge($totalVolume)['amount'] ?? 0.0;
        } catch (\Exception $e) {
            Log::warning('Error calculating SLPA charge: '.$e->getMessage());

            return 0.0;
        }
    }

    private function calculateBondCharge(GatePassChargesService $service, $hbl): float
    {
        try {
            $totalVolume = $hbl->packages->sum('volume');
            $totalWeight = $hbl->packages->sum('weight');

            return $service->bondCharge($totalVolume, $totalWeight)['amount'] ?? 0.0;
        } catch (\Exception $e) {
            Log::warning('Error calculating bond charge: '.$e->getMessage());

            return 0.0;
        }
    }

    private function calculateDemurrageCharge(
        GatePassChargesService $service,
        $hbl,
        $container,
        int $arrivalDatesCount
    ): float {
        if (! $container) {
            return 0.0;
        }

        try {
            $totalVolume = $hbl->packages->sum('volume');
            $totalWeight = $hbl->packages->sum('weight');

            return $service->demurrageCharge($arrivalDatesCount, $totalVolume, $totalWeight)['amount'] ?? 0.0;
        } catch (\Exception $e) {
            Log::warning('Error calculating demurrage charge: '.$e->getMessage());

            return 0.0;
        }
    }

    private function getDOCharge(GatePassChargesService $service, $hbl): float
    {
        try {
            if ($hbl->containers()->count() === 0) {
                return 0.0;
            }
            return $service->dOCharge($hbl)['amount'] ?? 0.0;
        } catch (\Exception $e) {
            Log::warning('Error getting DO charge: '.$e->getMessage());

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
            'dOCharge' => 0.0,
            'vatCharge' => 0.0,
            'totalAmount' => 0.0,
        ];
    }
}
