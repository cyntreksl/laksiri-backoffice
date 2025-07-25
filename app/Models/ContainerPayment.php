<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContainerPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'container_payments';

    protected $fillable = [
        'container_id',
        'do_charge',
        'demurrage_charge',
        'assessment_charge',
        'slpa_charge',
        'refund_charge',
        'clearance_charge',
        'total',
        'created_by',
        'is_refund_collected',
        'refund_collected_by',
        'refund_collected_date',
        'is_paid',
        'paid_by',
        'payment_received_by',
        'paid_date',
        'do_charge_finance_approved',
        'do_charge_requested_at',
        'do_charge_approved_at',
        'do_charge_requested_by',
        'do_charge_approved_by',
        'demurrage_charge_finance_approved',
        'demurrage_charge_requested_at',
        'demurrage_charge_approved_at',
        'demurrage_charge_requested_by',
        'demurrage_charge_approved_by',
        'assessment_charge_finance_approved',
        'assessment_charge_requested_at',
        'assessment_charge_approved_at',
        'assessment_charge_requested_by',
        'assessment_charge_approved_by',
        'slpa_charge_finance_approved',
        'slpa_charge_requested_at',
        'slpa_charge_approved_at',
        'slpa_charge_requested_by',
        'slpa_charge_approved_by',
        'refund_charge_finance_approved',
        'refund_charge_requested_at',
        'refund_charge_approved_at',
        'refund_charge_requested_by',
        'refund_charge_approved_by',
        'clearance_charge_finance_approved',
        'clearance_charge_requested_at',
        'clearance_charge_approved_at',
        'clearance_charge_requested_by',
        'clearance_charge_approved_by',
    ];

    protected $casts = [
        'is_finance_approved' => 'boolean',
    ];

    public function container()
    {
        return $this->belongsTo(Container::class, 'container_id');
    }
}
