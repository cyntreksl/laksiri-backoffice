<?php

namespace App\Services;

use App\Actions\AirLine\GetAirLineByName;
use App\Actions\Branch\GetBranchById;
use App\Actions\HBL\HBLCharges\CalculateTax;
use App\Actions\SpecialDOCharge\GetSpecialDOChargeByAgent;
use App\Actions\Tax\GetSumOfTaxRatesByWarehouse;
use App\Models\HBL;
use Illuminate\Support\Facades\Auth;

class GatePassChargesService
{
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
            'slpa_charge' => 600.00,
            'reimbursement_logic' => null,
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
            'reimbursement_logic' => 'kg > 79 ? ((kg * 27) + 2000) : 0',
        ],
    ];

    /**
     * Create a new instance with VAT and cargo mode.
     */
    public function __construct(string $cargo_mode = 'Sea Cargo',$destinationBranchId= null)
    {
        $this->cargo_mode = $cargo_mode;
        if(!empty($destinationBranchId)){
            $destinationBranch = GetBranchById::run($destinationBranchId);
            $branchDestinationPrice = $destinationBranch->branchDestinationPrices;
            if ($branchDestinationPrice) {
                $this->chargeModes['Sea Cargo']['port_charge'] = $branchDestinationPrice->sea_cargo_port_charge ?? $this->chargeModes['Sea Cargo']['port_charge'];
                $this->chargeModes['Sea Cargo']['handling_charge'] = $branchDestinationPrice->sea_cargo_handling_charge ?? $this->chargeModes['Sea Cargo']['handling_charge'];
                $this->chargeModes['Sea Cargo']['bond_charge'] = $branchDestinationPrice->sea_cargo_bond_charge ?? $this->chargeModes['Sea Cargo']['bond_charge'];
                $this->chargeModes['Sea Cargo']['slpa_charge'] = $branchDestinationPrice->sea_cargo_slpa_charge ?? $this->chargeModes['Sea Cargo']['slpa_charge'];
                $this->chargeModes['Air Cargo']['port_charge'] = $branchDestinationPrice->air_cargo_port_charge ?? $this->chargeModes['Air Cargo']['port_charge'];
                $this->chargeModes['Air Cargo']['handling_charge'] = $branchDestinationPrice->air_cargo_handling_charge ?? $this->chargeModes['Air Cargo']['handling_charge'];
                $this->chargeModes['Air Cargo']['bond_charge'] = $branchDestinationPrice->air_cargo_bond_charge ?? $this->chargeModes['Air Cargo']['bond_charge'];
                $this->chargeModes['Air Cargo']['slpa_charge'] = $branchDestinationPrice->air_cargo_slpa_charge ?? $this->chargeModes['Air Cargo']['slpa_charge'];
                $this->chargeModes['Air Cargo']['reimbursement_logic'] = $branchDestinationPrice->air_cargo_reimbursement_logic ?? $this->chargeModes['Air Cargo']['reimbursement_logic'];
            }
        }

        $this->setCharges($cargo_mode);
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
            'amount' => round($this->charges['port_charge'] * $volume, 2),
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
            'amount' => round($this->charges['handling_charge'] * $package_count, 2),
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
            'amount' => round($quantity * $this->charges['bond_charge'], 2),
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

        $tax = CalculateTax::run($rate);
        $amount = $tax['amount_with_tax'];

        return [
            'rate' => round($amount, 2),
            'amount' => round($amount, 2),
        ];
    }

    public function specialCharge(int $containerArrivalDatesCount, float $grand_volume, float $grand_weight): array
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

        $tax = CalculateTax::run($rate);
        $amount = $tax['amount_with_tax'];

        return [
            'rate' => round($amount, 2),
            'amount' => round($amount, 2),
        ];
    }

    /**
     * Get sea carggo DO charge details.
     */
    private function seaCargoDOCharge(HBL $hbl): array
    {
        $doChargeData = [
            'agent_id' => $hbl->branch_id,
            'cargo_type' => $hbl->cargo_type,
            'hbl_type' => $hbl->hbl_type,
        ];
        $groupedRules = collect(GetSpecialDOChargeByAgent::run($doChargeData))->groupBy('package_type');

        $rate = 0;
        $amount = 0;
        foreach ($groupedRules as $index => $ruleSet) {
            if ($index === 'HBL' && isset($ruleSet[0])) {
                $rate += $ruleSet[0]->charge;
                $amount += $ruleSet[0]->charge;
            } elseif ($index === 'DO' && isset($ruleSet[0])) {
                $rate += $ruleSet[0]->charge;
                $amount += $ruleSet[0]->charge;
            } else {
                $package_quantity = $hbl->packages
                    ->where('package_type', $index)
                    ->sum('quantity');
                $groupedDORules = $ruleSet->groupBy('condition');
                $operations = array_keys($groupedDORules->toArray());

                usort($operations, function ($a, $b) {
                    return ((int) filter_var($b, FILTER_SANITIZE_NUMBER_INT)) <=> ((int) filter_var($a, FILTER_SANITIZE_NUMBER_INT));
                });
                $operations = array_values(array_filter($operations, function ($operation) use ($package_quantity) {
                    $number = floatval(substr($operation, 1));

                    return $number < $package_quantity;
                }));
                $packageDOCharge = 0;
                foreach ($operations as $operation) {
                    $operation_quantity = (float) filter_var($operation, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    $rule = (object) $groupedDORules[$operation][0];
                    $quantity_after_operation = $package_quantity - $operation_quantity;
                    $packageDOCharge += ($quantity_after_operation * $rule['charge']);
                    $package_quantity = $operation_quantity;
                }
                $rate += $packageDOCharge;
                $amount += $packageDOCharge;
            }
        }

        return [
            'rate' => $rate,
            'amount' => $amount,
        ];
    }

    /**
     * Get air cargo DO charge details.
     */
    private function airCargoDOCharge(HBL $hbl): array
    {
        $container = $this->getContainer($hbl);

        if (! $container || ! $container->airline_name) {
            return [
                'rate' => 0.00,
                'amount' => 0.00,
            ];
        }

        $airCargoDORule = GetAirLineByName::run($container->airline_name)?->airLineDOCharge;

        if ($airCargoDORule) {
            return [
                'rate' => $airCargoDORule->do_charge,
                'amount' => $airCargoDORule->do_charge,
            ];
        }

        return [
            'rate' => 0.00,
            'amount' => 0.00,
        ];
    }

    /**
     * Get DO charge details.
     */
    public function dOCharge(HBL $hbl): array
    {
        if ($this->cargo_mode === 'Sea Cargo') {
            return $this->seaCargoDOCharge($hbl);
        } else {
            return $this->airCargoDOCharge($hbl);
        }
    }

    private function getContainer($hbl)
    {
        return $hbl->packages[0]->containers()->withoutGlobalScopes()->first() ?? $hbl->packages[0]->duplicate_containers()->withoutGlobalScopes()->first();
    }

    public function calculateReimbursement(float $kg): float
    {
        $logic = $this->chargeModes[$this->cargo_mode]['reimbursement_logic'];

        if (!$logic) {
            return 0.0;
        }

        $executor = new MathExecutor();

        try {
            return $executor->execute($logic, ['kg' => $kg]);
        } catch (\Throwable $e) {
            // fallback or log error
            return 0.0;
        }
    }
}
