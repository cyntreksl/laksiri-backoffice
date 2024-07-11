<?php

namespace App\Models;

use App\Traits\HasFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HBLDocument extends Model
{
    use HasFactory;
    use HasFile;

    protected $table = 'hbl_documents';

    protected $fillable = [
        'hbl_id', 'uploaded_by', 'document_name', 'document',
    ];
}
