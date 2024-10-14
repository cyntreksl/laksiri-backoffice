<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CallFlag extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'hbl_id',
        'caller',
        'date',
        'notes',
        'followup_date',
        'created_by',
    ];

    public function hbl(): BelongsTo
    {
        return $this->belongsTo(HBL::class, 'hbl_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
