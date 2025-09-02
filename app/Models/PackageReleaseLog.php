<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageReleaseLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_queue_id',
        'type',
        'packages',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'packages' => 'array',
    ];

    public function packageQueue(): BelongsTo
    {
        return $this->belongsTo(PackageQueue::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
