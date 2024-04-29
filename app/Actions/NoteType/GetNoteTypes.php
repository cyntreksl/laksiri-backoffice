<?php

namespace App\Actions\NoteType;

use App\Models\NoteType;
use Lorisleiva\Actions\Concerns\AsAction;

class GetNoteTypes
{
    use AsAction;

    public function handle()
    {
        return NoteType::all();
    }
}
