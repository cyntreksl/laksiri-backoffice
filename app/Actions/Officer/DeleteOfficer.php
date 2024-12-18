<?php

namespace App\Actions\Officer;

use App\Models\Officer;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteOfficer
{
    use AsAction;
    public function handle ($id):void
    {
        $officer = Officer::withoutGlobalScopes()->findOrFail($id);
        $officer->delete();
    }

}
