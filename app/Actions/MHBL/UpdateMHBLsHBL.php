<?php

namespace App\Actions\MHBL;

use App\Models\MHBL;
use App\Models\MHBLsHBL;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateMHBLsHBL
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(MHBL $mhbl, array $data)
    {
        $existhbls = $mhbl->hbls->pluck('id')->toArray();

        $removedHBLs = array_diff($existhbls, $data);
        $addedHBLs = array_diff($data, $existhbls);

        if (count($addedHBLs) > 0) {
            CreateMHBLsHBL::run($mhbl, $addedHBLs);
        }

        if (count($removedHBLs) > 0) {
            MHBLsHBL::where('mhbl_id', $mhbl->id)
                ->whereIn('hbl_id', $removedHBLs)
                ->delete();
        }
    }
}
