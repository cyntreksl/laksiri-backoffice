<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;

#[ScopedBy(BranchScope::class)]
class CourierAgent extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'courier_agents';

    protected $fillable = [
        'branch_id',
        'company_name',
        'website',
        'contact_number_1',
        'contact_number_2',
        'email',
        'address',
        'logo',
        'invoice_header',
        'invoice_footer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }
}
