<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy(BranchScope::class)]
class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id', 'invoice_header_title', 'invoice_header_subtitle', 'invoice_header_address', 'invoice_header_telephone', 'invoice_footer_title', 'invoice_footer_text', 'logo',
    ];
}
