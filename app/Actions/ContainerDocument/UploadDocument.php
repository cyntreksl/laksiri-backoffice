<?php

namespace App\Actions\ContainerDocument;

use App\Models\ContainerDocument;
use Lorisleiva\Actions\Concerns\AsAction;

class UploadDocument
{
    use AsAction;

    public function handle(array $data): void
    {
        if (isset($data['container_id'])) {
            $container_document = ContainerDocument::firstOrNew(
                [
                    'document_name' => $data['document_name'],
                ],
                [
                    'uploaded_by' => auth()->id(),
                    'container_id' => $data['container_id'],
                ]
            );

            $container_document->updateFile($data['document'], 'document', 'container/docs');
            $container_document->save();
        }
    }
}
