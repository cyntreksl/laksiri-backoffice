<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use App\Traits\HasFile;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[ScopedBy(BranchScope::class)]
class Setting extends Model
{
    use HasFactory;
    use HasFile;

    protected $fillable = [
        'branch_id', 'invoice_header_title', 'invoice_header_subtitle', 'invoice_header_address', 'invoice_header_telephone', 'invoice_footer_title', 'invoice_footer_text', 'logo',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'logo_url',
    ];

    public function logoUrl(): Attribute
    {
        return Attribute::get(function (): string {
            return Storage::disk('public')->url($this->logo);
        });
    }
}
