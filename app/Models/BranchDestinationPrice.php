<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchDestinationPrice extends Model
{
    protected $fillable = [
        'branch_id',
        'sea_cargo_port_charge',
        'sea_cargo_handling_charge',
        'sea_cargo_bond_charge',
        'sea_cargo_slpa_charge',
        'sea_cargo_reimbursement_logic',
        'air_cargo_port_charge',
        'air_cargo_handling_charge',
        'air_cargo_bond_charge',
        'air_cargo_slpa_charge',
        'air_cargo_reimbursement_logic',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
