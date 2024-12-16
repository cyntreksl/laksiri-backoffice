<?php

namespace App\Models;

use App\Traits\HasFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippersConsignees extends Model
{
    use HasFactory;
    use HasFile;

    protected $fillable = [
        'type', 'name', 'email', 'mobile_number', 'pp_or_nic_no', 'residency_no', 'address',
    ];
}
