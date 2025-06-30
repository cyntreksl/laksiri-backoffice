<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HBLDepartureCharge extends Model
{
    protected $table = 'hbl_departure_charges';

    protected $fillable = [
        'hbl_id',
        'branch_id',
        'base_currency_code',
        'base_currency_rate_in_lkr',
        'is_branch_prepaid',
        'freight_charge',
        'bill_charge',
        'package_charge',
        'discount',
        'additional_charges',
        'departure_grand_total',
    ];

    public function hbl()
    {
        return $this->belongsTo(HBL::class, 'hbl_id');
    }
}
