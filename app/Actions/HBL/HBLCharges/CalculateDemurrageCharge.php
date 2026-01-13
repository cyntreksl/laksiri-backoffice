<?php

namespace App\Actions\HBL\HBLCharges;

use App\Models\HBL;
use Carbon\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class CalculateDemurrageCharge
{
    use AsAction;

    private array $charges = [
        'Sea Cargo' => [
            'port_charge' => 600.00,
            'handling_charge' => 670.00,
            'bond_charge' => 9450.00,
            'demurrage_charge' => [0.00, 9.50, 10.00, 0.00],
            'demurrage_charge_first' => 0.00,
            'demurrage_charge_second' => 9.50,
            'demurrage_charge_third' => 10.00,
            'slpa_charge' => 600.00,
        ],
        'Air Cargo' => [
            'port_charge' => 0.00,
            'handling_charge' => 450.00,
            'bond_charge' => 29.00,
            'demurrage_charge' => [0.00, 8.00, 16.00, 24.00],
            'demurrage_charge_first' => 0.00,
            'demurrage_charge_second' => 8.00,
            'demurrage_charge_third' => 6.00,
            'demurrage_charge_fourth' => 24.00,
            'slpa_charge' => 0.00,
        ],
    ];

    public function handle(HBL $HBL)
    {
        // Get containers through packages
        $firstContainer = $this->getFirstContainer($HBL);
        
        // Use arrived_at_primary_warehouse, fallback to reached_date if not available
        $containerArrivalDate = null;
        if ($firstContainer) {
            $containerArrivalDate = $firstContainer->arrived_at_primary_warehouse 
                ?? $firstContainer->reached_date 
                ?? null;
        }

        // Calculate volume/weight from packages if HBL totals are not set
        $grand_volume = $HBL->grand_total_volume;
        $grand_weight = $HBL->grand_total_weight;
        
        if (empty($grand_volume) && empty($grand_weight)) {
            $packages = $HBL->packages;
            $grand_volume = $packages->sum('volume');
            $grand_weight = $packages->sum('weight');
        }

        if (! empty($containerArrivalDate)) {
            $arrivedAt = Carbon::parse($containerArrivalDate);
            $currentDate = Carbon::now();
            $containerArrivalDatesCount = (int) $arrivedAt->diffInDays($currentDate);

            if ($containerArrivalDatesCount > 1) {
                return $this->demurrageCharge($HBL, $containerArrivalDatesCount, $grand_volume, $grand_weight);
            } else {
                return [
                    'rate' => 0.00,
                ];
            }
        } else {
            return [
                'rate' => 0.00,
            ];
        }
    }

    private function getFirstContainer(HBL $HBL)
    {
        // Get containers through packages since HBL->containers() relationship is incorrect
        // Check both regular containers and duplicate containers (used for unloading history)
        $packages = $HBL->packages;
        
        foreach ($packages as $package) {
            // First try regular containers
            $container = $package->containers()->withoutGlobalScopes()->first();
            if ($container) {
                return $container;
            }
            
            // Fallback to duplicate containers (used when packages are unloaded)
            $container = $package->duplicate_containers()->withoutGlobalScopes()->first();
            if ($container) {
                return $container;
            }
        }
        
        return null;
    }

    private function demurrageCharge(HBL $HBL, int $containerArrivalDatesCount, float $grand_volume, float $grand_weight): array
    {
        $cargoType = $HBL->cargo_type;
        
        if (! isset($this->charges[$cargoType])) {
            return [
                'rate' => 0.00,
            ];
        }

        $quantity = $cargoType === 'Sea Cargo' ? ($grand_volume * 35) : $grand_weight;
        $rate = 0.0;

        $demurrageCharges = $this->charges[$cargoType]['demurrage_charge'];
        $chargeBrackets = [
            ['days' => 7, 'rate' => $demurrageCharges[0]],
            ['days' => 7, 'rate' => $demurrageCharges[1]],
            ['days' => 7, 'rate' => $demurrageCharges[2]],
            ['days' => 7, 'rate' => $demurrageCharges[3]],
        ];

        foreach ($chargeBrackets as $bracket) {
            if ($containerArrivalDatesCount <= 0) {
                break;
            }

            $applicableDays = min($bracket['days'], $containerArrivalDatesCount);

            $rate += $bracket['rate'] * $applicableDays * $quantity;

            $containerArrivalDatesCount -= $applicableDays;
        }

        return [
            'rate' => round($rate, 2),
        ];
    }
}
