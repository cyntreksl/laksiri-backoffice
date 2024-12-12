<?php

namespace App\Services;

class GatePassChargesService
{
    private float $vat;
    private float $port_charge;
    private float $handling_charge;
    private float $bond_charge;
    private float $demurrage_charge_first;
    private float $demurrage_charge_second;
    private float $demurrage_charge_third;

    /**
     * Create a new instance.
     *
     * @param float $vat
     * @param float $port_charge
     * @param float $handling_charge
     * @param float $bond_charge
     */
    public function __construct(float $port_charge = 600, float $vat = 18, float $handling_charge = 670, float $bond_charge = 9450, float $demurrage_charge_first = 0, float $demurrage_charge_second = 9.5, float $demurrage_charge_third = 10)
    {
        $this->vat = $vat;
        $this->port_charge = $port_charge;
        $this->handling_charge = $handling_charge;
        $this->bond_charge = $bond_charge;
        $this->demurrage_charge_first = $demurrage_charge_first;
        $this->demurrage_charge_second = $demurrage_charge_second;
        $this->demurrage_charge_third = $demurrage_charge_third;
    }

    /**
     * Get port charge details.
     *
     * @return array
     */
    public function portCharge(): array
    {
        $amount = $this->port_charge * (1 + $this->vat / 100);
        return [
            'rate' => round($this->port_charge, 2),
            'amount' => round($amount, 2),
        ];
    }

    /**
     * Get handling charge details.
     *
     * @return array
     */
    public function handlingCharge(): array
    {
        $amount = $this->handling_charge * (1 + $this->vat / 100);
        return [
            'rate' => round($this->handling_charge, 2),
            'amount' => round($amount, 2),
        ];
    }

    /**
     * Get bond charge details.
     *
     * @return array
     */
    public function bondCharge(): array
    {
        $amount = $this->bond_charge * (1 + $this->vat / 100);
        return [
            'rate' => round($this->bond_charge, 2),
            'amount' => round($amount, 2),
        ];
    }

    /**
     * Get Demurrag charge details.
     *
     * @return array
     */
    public function demurragCharge(): array
    {
        $amount = $this->bond_charge * (1 + $this->vat / 100);
        return [
            'rate' => round($this->bond_charge, 2),
            'amount' => round($amount, 2),
        ];
    }

    /**
     * Get VAT charge details.
     *
     * @return array
     */
    public function vatCharge(): array
    {
        return [
            'rate' => $this->vat,
        ];
    }
}
