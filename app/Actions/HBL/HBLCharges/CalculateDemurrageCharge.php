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
        $firstContainer = $HBL->containers()->first();
        $containerArrivalDate = $firstContainer ? $firstContainer->arrived_at_primary_warehouse : null;

        $grand_volume = $HBL->grand_total_volume;
        $grand_weight = $HBL->grand_total_weight;

        if (! empty($containerArrivalDate)) {
            $arrivedAt = Carbon::parse($containerArrivalDate);
            $currentDate = Carbon::now();
            $containerArrivalDatesCount = $arrivedAt->diffInDays($currentDate);

            if ($containerArrivalDatesCount > 1) {
                return $this->demurrageCharge($containerArrivalDatesCount, $grand_volume, $grand_weight);
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

    private function demurrageCharge(int $containerArrivalDatesCount, float $grand_volume, float $grand_weight): array
    {

        $quantity = $this->cargo_mode === 'Sea Cargo' ? ($grand_volume * 35) : $grand_weight;
        $rate = 0.0;

        $chargeBrackets = [
            ['days' => 7, 'rate' => $this->charges['demurrage_charge'][0]],
            ['days' => 7, 'rate' => $this->charges['demurrage_charge'][1]],
            ['days' => 7, 'rate' => $this->charges['demurrage_charge'][2]],
            ['days' => 7, 'rate' => $this->charges['demurrage_charge'][3]],
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
