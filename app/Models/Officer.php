<?php

namespace App\Models;

use App\Traits\HasFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Officer extends Model
{
    use HasFactory;
    use HasFile;
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'officers';

    protected $fillable = [
        'type', 'name', 'email', 'mobile_number', 'pp_or_nic_no', 'residency_no', 'address',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }
}
