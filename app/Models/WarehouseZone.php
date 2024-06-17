<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class WarehouseZone extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'branch_id',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }
}
