<?php

namespace App\Actions\FileManager;

use App\Models\ContainerDocument;
use Lorisleiva\Actions\Concerns\AsAction;

class AnyFiles
{
    use AsAction;

    public function handle(array $data, int $id)
    {
        if (isset($data['files'])) {
            $container_document = ContainerDocument::create([
                'document_name' => $data['files']->getClientOriginalName(),
                'uploaded_by' => auth()->id(),
                'container_id' => $id,
            ]);

            $container_document->updateFile($data['files'], 'document', 'container/docs');
            $container_document->save();

            return $container_document;
        }
    }
}
