<?php

namespace App\Actions\FileManager;

use App\Models\ContainerDocument;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class AnyFiles
{
    use AsAction;

    public function handle(array $data, int $id)
    {
        if (isset($data['files'])) {
            $path = $data['files']->store('uploads', 'public');
            $document = new ContainerDocument();
            $document->document_name = $data['files']->getClientOriginalName(); // Original file name
            $document->document = $path; // Path to the stored file
            $document->container_id = $id;
            $document->uploaded_by = Auth::user()->id;
            $document->save();

            return $document;
        }
    }
}
