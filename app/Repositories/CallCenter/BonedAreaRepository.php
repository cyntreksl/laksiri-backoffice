<?php

namespace App\Repositories\CallCenter;

use App\Actions\PackageQueue\UpdatePackageQueue;
use App\Interfaces\CallCenter\BonedAreaRepositoryInterface;
use Illuminate\Support\Facades\DB;

class BonedAreaRepository implements BonedAreaRepositoryInterface
{
    public function releasePackage(array $data): void
    {
        try {
            DB::beginTransaction();

            $data['is_released'] = true;

            $data['released_at'] = now();

            UpdatePackageQueue::run($data, $data['package_queue']['id']);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to update package queue when releasing package: '.$e->getMessage());
        }
    }
}
