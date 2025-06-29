<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HblCharge extends Model
{
    protected $fillable = [
        'hbl_id',
        'branch_id',
        'base_currency_code',
        'base_currency_rate_in_lkr',
        'is_branch_prepaid',
        'applicable_taxes',

        'freight_charge',
        'bill_charge',
        'package_charge',
        'departure_1_total',

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

        // departure_total_charge
        // if prepaid departure_1_total + destination_1_total_with_tax(base currency)
        // if postpaid departure_1_total
        'departure_total_charge',
        'departure_discount',
        'departure_additional_charge',
        'departure_net_total',
        'departure_paid_amount',
        'departure_due',

        // destination_total_charge
        // if prepaid destination_2_total
        // if postpaid destination_1_total + destination_2_total
        'destination_total_charge',
        'destination_discount',
        'destination_additional_charge',

        // destination_total_tax
        'destination_total_tax',
        'destination_net_total',
        'destination_paid_amount',
        'destination_due',

        'destination_due',
        'grand_total_paid',
        'grand_total_due',

    ];


}
