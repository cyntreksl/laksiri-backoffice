<?php

namespace App\Actions\HBLDocument;

use App\Models\HBLDocument;
use Lorisleiva\Actions\Concerns\AsAction;

class UploadDocument
{
    use AsAction;

    public function handle(array $data): void
    {
        if (isset($data['hbl_id'])) {
            $hbl_document = HBLDocument::firstOrNew(
                [
                    'document_name' => $data['document_name'],
                ],
                [
                    'uploaded_by' => auth()->id(),
                    'hbl_id' => $data['hbl_id'],
                ]
            );

            $hbl_document->updateFile($data['document'], 'document', 'hbl/docs');
            $hbl_document->save();
        }
    }
}
