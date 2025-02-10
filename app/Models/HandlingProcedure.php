<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HandlingProcedure extends Model
{
    protected $fillable = [
        'container_id',
        'step_id',
        'is_completed',
        'completed_by',
        'completed_at'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime'
    ];

    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
} 