<?php

namespace App\Actions\Tax;

use App\Models\Tax;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteTax
{
    use AsAction;

    public function handle(Tax $tax)
    {
        $tax->delete();
    }
}
