<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy(BranchScope::class)]
class HBLPayment extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'hbl_payments';

    protected $fillable = [
        'branch_id',
        'hbl_id',
        'grand_total',
        'paid_amount',
        'status',
        'created_by',
        'base_currency',
        'currency_rate',
        'currency_code',
        'freight_charge',
        'bill_charge',
        'other_charge',
        'discount',
        'additional_charge',
        'do_charge',
        'destination_charge',
        'is_departure_charges_paid',
        'is_destination_charges_paid',
        'package_charge',
        'handling_charge',
        'slpa_charge',
        'bond_charge',
        'demurrage_charge',
        'sub_total',
        'tax',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }
}
