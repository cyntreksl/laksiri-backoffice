<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HBLDestinationCharge extends Model
{
    protected $table = 'hbl_destination_charges';

    protected $fillable = [
        'hbl_id',
        'branch_id',
        'base_currency_code',
        'base_currency_rate_in_lkr',
        'is_branch_prepaid',
        'applicable_taxes',

        'destination_handling_charge',
        'destination_slpa_charge',
        'destination_bond_charge',
        'destination_1_total',
        'destination_1_tax',
        'destination_1_total_with_tax',

        'destination_do_charge',
        'destination_demurrage_charge',
        'destination_stamp_charge',
        'destination_other_charge',
        'destination_2_total',
        'destination_2_tax',
        'destination_2_total_with_tax',

        'stop_demurrage_at',
    ];

    protected $casts = [
        'applicable_taxes' => 'array',
        'stop_demurrage_at' => 'datetime',
    ];

    public function hbl()
    {
        return $this->belongsTo(HBL::class, 'hbl_id');
    }
}
