<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Examination extends Model
{
    use HasFactory;

    protected $fillable = [
        'released_by', 'customer_queue_id', 'token_id', 'hbl_id', 'package_queue_id', 'released_packages', 'released_at', 'is_issued_gate_pass', 'note',
    ];

    protected $casts = [
        'released_packages' => 'array',
    ];
}
