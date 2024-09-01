<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerFeedback extends Model
{
    use HasFactory;

    public $table = 'customer_feedbacks';

    protected $fillable = [
        'token_id',
        'user_id',
        'hbl_id',
        'rating',
        'feedback',
    ];
}
