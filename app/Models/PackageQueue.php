<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'token_id', 'hbl_id', 'auth_id', 'reference', 'package_count', 'is_released', 'released_at', 'note', 'released_packages',
    ];

    protected $casts = [
        'released_packages' => 'array',
    ];

    public function token(): BelongsTo
    {
        return $this->belongsTo(Token::class, 'token_id');
    }

    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auth_id');
    }

    public function releaseLogs()
    {
        return $this->hasMany(PackageReleaseLog::class);
    }
}
