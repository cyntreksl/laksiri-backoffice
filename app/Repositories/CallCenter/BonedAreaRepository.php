<?php

namespace App\Repositories\CallCenter;

use App\Actions\PackageQueue\UpdatePackageQueue;
use App\Http\Resources\CallCenter\PackageCollection;
use App\Interfaces\CallCenter\BonedAreaRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\PackageQueue;
use Illuminate\Support\Facades\DB;

class BonedAreaRepository implements BonedAreaRepositoryInterface, GridJsInterface
{
    public function releasePackage(array $data): void
    {
        try {
            DB::beginTransaction();

            $data['is_released'] = true;

            $data['released_at'] = now();

            $data['auth_id'] = auth()->id();

            UpdatePackageQueue::run($data, $data['package_queue']['id']);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to update package queue when releasing package: '.$e->getMessage());
        }
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = PackageQueue::query()
            ->whereHas('token')
            ->where('is_released', true);

        $records = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => PackageCollection::collection($records),
            'meta' => [
                'total' => $records->total(),
                'current_page' => $records->currentPage(),
                'perPage' => $records->perPage(),
                'lastPage' => $records->lastPage(),
            ],
        ]);
    }
}
