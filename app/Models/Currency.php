<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Currency extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'currency_rates';

    protected $fillable = [
        'currency_name',
        'currency_symbol',
        'sl_rate',
        'created_by',
    ];

    protected $casts = [
        'sl_rate' => 'float',
    ];

    /**
     * Optional: Relationship to user who created the rate
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }
}
