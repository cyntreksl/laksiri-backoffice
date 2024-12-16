<?php

namespace App\Actions\MHBL;

use App\Models\Mhbl;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateMHBL
{
    use AsAction;

    public function handle(array $data): MHBL
    {
        $reference = GenerateMHBLReferenceNumber::run();
        $data['reference'] = $reference;
        $mhbl = MHBL::create($data);

        return $mhbl;
    }
}
