<?php

namespace App\Actions\Tax;

use App\Models\Tax;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateTax
{
    use AsAction;

    public function handle(Tax $tax, array $data)
    {
        return $tax->update($data);
    }
}
