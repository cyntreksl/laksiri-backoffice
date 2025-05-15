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
        'is_finance_approved',
        'finance_approved_by',
        'finance_approved_date',
        'is_paid',
        'paid_by',
        'payment_received_by',
        'paid_date',
        'is_refund_collected',
        'refund_collected_by',
        'refund_collected_date',
        'created_by',
    ];

    protected $casts = [
        'is_finance_approved' => 'boolean',
    ];

    public function container()
    {
        return $this->belongsTo(Container::class, 'container_id');
    }
}
