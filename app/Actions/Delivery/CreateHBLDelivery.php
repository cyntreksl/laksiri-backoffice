<?php

namespace App\Actions\Delivery;

use App\Models\HBLDeliver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateHBLDelivery
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle($driver_id, array $hbl_ids)
    {
        DB::beginTransaction();

        try {
            foreach ($hbl_ids as $hbl_id) {
                $hblDelivery = new HBLDeliver();
                $hblDelivery->branch_id = Auth::user()->primary_branch_id;
                $hblDelivery->hbl_id = $hbl_id;
                $hblDelivery->driver_id = $driver_id;
                $hblDelivery->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
