<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Branch extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'branch_code',
        'parent_id',
        'type',
        'currency_name',
        'currency_symbol',
        'country_code',
        'cargo_modes',
        'delivery_types',
        'package_types',
        'do_charge',
        'email',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_branches');
    }

    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function warehouseZones(): HasMany
    {
        return $this->hasMany(WarehouseZone::class);
    }
}
