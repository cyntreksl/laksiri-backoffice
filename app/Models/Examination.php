<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Examination extends Model
{
    use HasFactory;

    protected $fillable = [
        'released_by', 'customer_queue_id', 'token_id', 'hbl_id', 'package_queue_id', 'released_packages', 'released_at', 'is_issued_gate_pass', 'note',
    ];

    protected $casts = [
        'released_packages' => 'array',
    ];

    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function packageExaminations(): HasMany
    {
        return $this->hasMany(PackageExamination::class);
    }

    public function hbl(): BelongsTo
    {
        return $this->belongsTo(HBL::class);
    }

    public function customerQueue(): BelongsTo
    {
        return $this->belongsTo(CustomerQueue::class);
    }

    public function token(): BelongsTo
    {
        return $this->belongsTo(Token::class);
    }
}
