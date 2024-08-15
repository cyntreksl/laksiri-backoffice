<?php

namespace App\Models;

use App\Traits\HasQueueLogs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    use HasFactory;
    use HasQueueLogs;

    protected $fillable = [
        'is_checked', 'verified_by', 'customer_queue_id', 'token_id', 'note',
    ];

    protected $casts = [
        'is_checked' => 'array',
    ];

    public static function verification_documents(): array
    {
        return [
            'Passport',
            'HBL Receipt',
        ];
    }
}
