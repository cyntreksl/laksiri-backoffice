<?php

namespace App\Interfaces;

use App\Models\Mhbl;

interface MHBLRepositoryInterface
{
    public function storeHBL(array $data);

    public function deleteMHBL(MHBL $MHBL);
}
