<?php

namespace App\Actions\MHBL;

use App\Models\MHBL;
use App\Models\MHBLsHBL;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateMHBLsHBL
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(MHBL $mhbl, array $data)
    {
        $packages = [];

        DB::beginTransaction();

        try {
            foreach ($data as $hblData) {
                $mhblsHBL = new MHBLsHBL;
                $mhblsHBL->mhbl_id = $mhbl->id;
                $mhblsHBL->hbl_id = $hblData;
                $mhblsHBL->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
