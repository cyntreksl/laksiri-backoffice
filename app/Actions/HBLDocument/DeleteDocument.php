<?php

namespace App\Actions\HBLDocument;

use App\Models\HBLDocument;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteDocument
{
    use AsAction;

    public function handle(HBLDocument $hblDocument): void
    {
        $hblDocument->deleteFile('hbl/docs', 'document');

        $hblDocument->delete();
    }
}
