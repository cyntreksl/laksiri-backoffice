<?php

namespace App\Repositories\CallCenter;

use App\Interfaces\CallCenter\ExaminationRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ExaminationRepository implements ExaminationRepositoryInterface
{
    public function releaseHBL(array $data): void
    {
        try {
            DB::beginTransaction();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to update payments: '.$e->getMessage());
        }
    }
}
