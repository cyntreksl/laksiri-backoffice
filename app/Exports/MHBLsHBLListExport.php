<?php

namespace App\Exports;

use App\Models\MHBL;

class MHBLsHBLListExport
{
    private MHBL $mhbl;

    public function __construct(MHBL $mhbl)
    {
        $this->mhbl = $mhbl;
    }

    public function query() {}

    public function prepareData(): array
    {
        $data = [];
        $hbls = $this->mhbl->hbls;
        foreach ($hbls as $hblNumber => $hbl) {
            $data[] = [
                $hbl->hbl_number,
                $hbl->packages->sum('weight'),
                $hbl->packages->sum('volume'),
                count($hbl->packages),
            ];
        }

        return $data;
    }
}
