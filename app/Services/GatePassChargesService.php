<?php

namespace App\Services;

use App\Actions\Branch\GetBranchById;
use Illuminate\Support\Facades\Auth;

class GatePassChargesService
{
    private float $vat;

    private string $cargo_mode;

    private array $charges;

    // Define charge rates for both sea and air cargo
    private array $chargeModes = [
        'Sea Cargo' => [
            'port_charge' => 600.00,
            'handling_charge' => 670.00,
            'bond_charge' => 9450.00,
            'demurrage_charge' => [0.00, 9.50, 10.00, 0.00],
            'demurrage_charge_first' => 0.00,
            'demurrage_charge_second' => 9.50,
            'demurrage_charge_third' => 10.00,
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
        ],
    ];

    private int $branch_demurrage_charge_discount;

    /**
     * Create a new instance with VAT and cargo mode.
     */
    public function __construct(string $cargo_mode = 'Sea Cargo', float $vat = 18)
    {
        $this->vat = $vat;
        $this->cargo_mode = $cargo_mode;
        $this->setCharges($cargo_mode);
        $this->branch_demurrage_charge_discount = GetBranchById::run(Auth::user()->primary_branch_id)['demurrage_discount'];
    }

    /**
     * Set charges based on cargo mode.
     */
    private function setCharges(string $cargo_mode): void
    {
        // Check if cargo mode exists
        if (! isset($this->chargeModes[$cargo_mode])) {
            throw new \InvalidArgumentException("Invalid cargo mode: $cargo_mode");
        }
        $this->charges = $this->chargeModes[$cargo_mode];
    }

    /**
     * Get port charge details.
     *
     * @param  float  $volume  MCU
     */
    public function portCharge(float $volume): array
    {
        return [
            'rate' => round($this->charges['port_charge'] * $volume, 2),
            'amount' => round($this->charges['port_charge'] * $volume * (1 + $this->vat / 100), 2),
        ];
    }

    /**
     * Get handling charge details.
     *
     * @param  int  $package_count  package count
     */
    public function handlingCharge(int $package_count): array
    {
        return [
            'rate' => round($this->charges['handling_charge'] * $package_count, 2),
            'amount' => round($this->charges['handling_charge'] * $package_count * (1 + $this->vat / 100), 2),
        ];
    }

    /**
     * Get bond charge details.
     *
     * @param  float  $grand_volume  packages volume
     * @param  float  $grand_weight  packages weight
     */
    public function bondCharge(float $grand_volume, float $grand_weight): array
    {
        $quantity = $this->cargo_mode === 'Sea Cargo' ? $grand_volume : $grand_weight;

        return [
            'rate' => round($quantity * $this->charges['bond_charge'], 2),
            'amount' => round($quantity * $this->charges['bond_charge'] * (1 + $this->vat / 100), 2),
        ];
    }

    /**
     * Get Demurrage charge details.
     *
     * @param  int  $containerArrivalDatesCount  Number of days since container arrival.
     * @param  float  $grand_volume  packages grand volume
     * @param  float  $grand_weight  packages grand weight
     */
    public function demurrageCharge(int $containerArrivalDatesCount, float $grand_volume, float $grand_weight): array
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

        $amount = $rate * (1 + $this->vat / 100);
        $discounted_rate = $rate * (100 - $this->branch_demurrage_charge_discount) / 100;

        return [
            'rate' => round($discounted_rate, 2),
            'amount' => round($amount, 2),
        ];
    }

    /**
     * Get VAT charge details.
     */
    public function vatCharge(): array
    {
        return [
            'rate' => $this->vat,
        ];
    }
}
