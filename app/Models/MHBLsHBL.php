<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MHBLsHBL extends Model
{
    use HasFactory, SoftDeletes;

    // The table associated with the model (optional if naming conventions are followed)
    protected $table = 'mhbls_hbl';

    // Mass assignable attributes
    protected $fillable = [
        'mhbl_id',
        'hbl_id',
    ];
}
