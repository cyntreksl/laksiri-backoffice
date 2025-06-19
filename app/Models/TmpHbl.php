<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TmpHbl extends Model
{
    use HasFactory;

    protected $table = 'tmp_hbl';

    protected $fillable = [
        'session_id',
        'hbl_number',
        'hbl_name',
        'email',
        'contact_number',
        'additional_mobile_number',
        'whatsapp_number',
        'nic',
        'iq_number',
        'address',
        'consignee_name',
        'consignee_nic',
        'consignee_contact',
        'consignee_additional_mobile_number',
        'consignee_whatsapp_number',
        'consignee_address',
        'consignee_note',
    ];

    public function packages()
    {
        return $this->hasMany(TmpHblPackage::class, 'hbl_number', 'hbl_number');
    }
}
