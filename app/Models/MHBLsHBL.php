<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MHBLsHBL extends Model
{
    use HasFactory, SoftDeletes;
    use LogsActivity;

    // The table associated with the model (optional if naming conventions are followed)
    protected $table = 'mhbls_hbl';

    // Mass assignable attributes
    protected $fillable = [
        'mhbl_id',
        'hbl_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }
}
