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
                HBLDeliver::updateOrCreate(
                    ['hbl_id' => $hbl_id],
                    [
                        'branch_id' => Auth::user()->primary_branch_id,
                        'driver_id' => $driver_id,
                    ]
                );
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
