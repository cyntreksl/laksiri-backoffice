<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SLInvoice extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sl_invoices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'hbl_id',
        'clearing_time',
        'date',
        'container_id',
        'grand_volume',
        'grand_weight',
        'port_charge_rate',
        'port_charge_amount',
        'handling_charge_rate',
        'handling_charge_amount',
        'storage_charge_rate',
        'storage_charge_amount',
        'dmg_charge_rate',
        'dmg_charge_amount',
        'total',
        'do_charge',
        'stamp_charge',
        'created_by',
    ];

    /**
     * Relationships
     */

    // Relationship with HBL (Assuming there's an Hbl model)
    public function hbl()
    {
        return $this->belongsTo(Hbl::class, 'hbl_id');
    }

    // Relationship with the User who created the record
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
