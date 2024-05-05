<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy(BranchScope::class)]
class HBL extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'hbl';

    protected $fillable = [
        'reference', 'branch_id', 'cargo_type', 'hbl_type', 'hbl', 'hbl_name', 'email', 'contact_number', 'nic', 'iq_number', 'address', 'consignee_name', 'consignee_nic', 'consignee_contact', 'consignee_address', 'consignee_note', 'warehouse', 'freight_charge', 'bill_charge', 'other_charge', 'discount', 'paid_amount', 'grand_total', 'created_by', 'deleted_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }
}
