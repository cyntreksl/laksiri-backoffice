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

    public function getBranchNameAttribute()
    {

        if ($this->relationLoaded('branch')) {
            return $this->branch->name;
        } else {

            return null;
        }
    }

    // Optional: Define visible attributes for toArray() and toJson() methods
    protected $visible = ['id', 'name', 'description', 'branch_id', 'branch_name'];

    // Optional: Define cast types for attributes
    protected $casts = [
        'branch_id' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }
}
