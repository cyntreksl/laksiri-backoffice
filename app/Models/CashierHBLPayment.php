<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashierHBLPayment extends Model
{
    use HasFactory;

    protected $table = 'cashier_hbl_payments';

    protected $fillable = [
        'verified_by', 'customer_queue_id', 'token_id', 'hbl_id', 'paid_amount', 'note',
    ];

    public function token(): BelongsTo
    {
        return $this->belongsTo(Token::class, 'token_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
