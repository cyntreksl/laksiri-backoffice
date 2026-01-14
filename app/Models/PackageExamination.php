<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageExamination extends Model
{
    use HasFactory;

    protected $fillable = [
        'hbl_package_id',
        'examination_id',
        'customer_queue_id',
        'token_id',
        'action',
        'note',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function hblPackage(): BelongsTo
    {
        return $this->belongsTo(HBLPackage::class, 'hbl_package_id');
    }

    public function examination(): BelongsTo
    {
        return $this->belongsTo(Examination::class);
    }

    public function customerQueue(): BelongsTo
    {
        return $this->belongsTo(CustomerQueue::class);
    }

    public function token(): BelongsTo
    {
        return $this->belongsTo(Token::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
