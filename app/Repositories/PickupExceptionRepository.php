<?php

namespace App\Repositories;

use App\Actions\PickUps\Exception\AssignDriver;
use App\Actions\PickUps\Exception\DeleteException;
use App\Actions\PickUps\Exception\GetExceptionsByIds;
use App\Actions\PickUps\Exception\RetryException;
use App\Exports\PickupExceptionsExport;
use App\Factory\Pickup\FilterFactory;
use App\Http\Resources\PickupExceptionResource;
use App\Interfaces\GridJsInterface;
use App\Interfaces\PickupExceptionRepositoryInterface;
use App\Models\PickUp;
use App\Models\PickupException;
use Maatwebsite\Excel\Facades\Excel;

class PickupExceptionRepository implements GridJsInterface, PickupExceptionRepositoryInterface
{
    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = PickupException::query();

        if (! empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%');
            });
        }

        // apply filters
        FilterFactory::apply($query, $filters);

        $exceptions = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => PickupExceptionResource::collection($exceptions),
            'meta' => [
                'total' => $exceptions->total(),
                'current_page' => $exceptions->currentPage(),
                'perPage' => $exceptions->perPage(),
                'lastPage' => $exceptions->lastPage(),
            ],
        ]);
    }

    public function assignDriverToExceptions(array $data)
    {
        $pickupList = GetExceptionsByIds::run($data['job_ids']);

        foreach ($pickupList as $pickup) {
            AssignDriver::run($pickup, $data['driver_id']);
        }
    }

    public function deleteExceptions(array $exceptionIDs)
    {
        $exceptionList = GetExceptionsByIds::run($exceptionIDs);

        foreach ($exceptionList as $exception) {
            DeleteException::run($exception);
        }
    }

    public function export(array $filters)
    {
        return Excel::download(new PickupExceptionsExport($filters), 'pickup-exception.xlsx');
    }

    public function retryException(PickUp $pickup): void
    {
        if ($pickup->has('pickupException')) {
            RetryException::run($pickup);
        }
    }
}
