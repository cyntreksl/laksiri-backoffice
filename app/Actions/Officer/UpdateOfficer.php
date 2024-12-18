<?php

namespace App\Actions\Officer;

use App\Models\Officer;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateOfficer
{
    use AsAction;

    public function handle(array $data, $id)
    {
        $officer = Officer::withoutGlobalScopes()->findOrFail($id);

        return $officer->update($data);
    }
}
