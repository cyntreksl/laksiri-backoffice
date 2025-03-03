<?php

namespace App\Models;

use App\Traits\HasFile;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CourierAgent extends Model
{
    use HasFactory, SoftDeletes, HasFile,LogsActivity ;

    protected $table = 'courier_agents';

    protected $fillable = [
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
    protected $appends = ['logo_url'];

    public function getLogoUrlAttribute(): string
    {
        return $this->logo ? Storage::disk('s3')->url($this->logo) : '';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }
}
