<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AirLineDOCharge extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'air_line_id',
        'do_charge',
        'created_by',
    ];

    protected $casts = [
        'do_charge' => 'float',
    ];

    /**
     * Get the airline associated with this DO charge.
     */
    public function airline()
    {
        return $this->belongsTo(AirLine::class, 'air_line_id');
    }

    /**
     * Get the user who created this DO charge.
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
