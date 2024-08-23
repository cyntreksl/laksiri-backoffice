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

    public function token(): BelongsTo
    {
        return $this->belongsTo(Token::class, 'token_id');
    }
}
