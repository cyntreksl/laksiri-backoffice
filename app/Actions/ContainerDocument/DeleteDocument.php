<?php

namespace App\Actions\ContainerDocument;

use App\Models\ContainerDocument;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteDocument
{
    use AsAction;

    public function handle(ContainerDocument $containerDocument): void
    {
        $containerDocument->deleteFile('/container/docs', 'document');

        $containerDocument->delete();
    }
}
