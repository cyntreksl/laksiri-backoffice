<?php

namespace App\Models;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy(BranchScope::class)]
class RtfRecord extends Model
{
    use LogsActivity;

    protected $fillable = ['is_rtf', 'rtf_by', 'note'];

    protected $table = 'rtf_records';

    protected $casts = [
        'is_rtf' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($record) {
            if (empty($record->branch_id)) {
                $record->branch_id = GetUserCurrentBranchID::run();
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    public function rtfable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rtf_by');
    }

    public function branch(): HasOne
    {
        return $this->hasOne(Branch::class, 'id', 'branch_id');
    }
}
