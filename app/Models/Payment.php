<?php

namespace App\Models;

use App\Actions\Branch\GetBranchById;
use App\Actions\User\GetUserCurrentBranch;
use App\Actions\User\GetUserCurrentBranchID;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    use LogsActivity;

    protected $fillable = [
        'branch_id',
        'hbl_id',
        'base_currency_code',
        'base_currency_rate_in_lkr',
        'paid_amount',
        'total_amount',
        'due_amount',
        'payment_method',
        'side',
        'paid_by',
        'paid_at',
        'notes',
        'is_cancelled',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected static function booted(): void
    {
        static::creating(function ($record) {
            $currentBranch = GetBranchById::run(GetUserCurrentBranchID::run());

            if (empty($record->branch_id)) {
                $record->branch_id = GetUserCurrentBranchID::run();
                $record->side = GetUserCurrentBranch::run()['branchType'];
                $record->base_currency_code = $currentBranch->currency_symbol ?? null;
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }
}
