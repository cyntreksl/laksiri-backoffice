<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'branch_id',
    ];

    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class)->withTimestamps();
    }
}
