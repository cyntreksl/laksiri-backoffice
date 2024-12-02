<?php

namespace App\Actions\HBLDocument;

use App\Models\HBLDocument;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;

class DownloadDocument
{
    use AsAction;

    public function handle(HBLDocument $hbl_document)
    {
        $filePath = $hbl_document->document;

        if (Storage::disk('s3')->exists($filePath)) {
            return Storage::disk('s3')->download($filePath);
        }
    }
}
