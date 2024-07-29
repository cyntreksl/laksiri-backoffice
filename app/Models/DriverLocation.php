<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverLocation extends Model
{
    use HasFactory;

    protected $fillable = ['driver_id', 'latitude', 'longitude', 'meta_data'];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
