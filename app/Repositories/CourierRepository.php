<?php

namespace App\Repositories;

use App\Actions\Courier\CreateCourier;
use App\Actions\Courier\CreateCourierPackages;
use App\Actions\Courier\DeleteCourier;
use App\Actions\Courier\GetCourierByCourierNumber;
use App\Actions\Courier\UpdateCourier;
use App\Actions\Courier\UpdateCourierStatus;
use App\Actions\CourierPackage\UpdateCourierPackages;
use App\Factory\Courier\FilterFactory;
use App\Http\Resources\CourierCollection;
use App\Interfaces\CourierRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\Courier;

class CourierRepository implements CourierRepositoryInterface, GridJsInterface
{
    public function storeCourier(array $data)
    {
        $data['status'] = Courier::PENDING;
        $courier = CreateCourier::run($data);
        $packagesData = $data['packages'];
        CreateCourierPackages::run($courier, $packagesData);
        $courier->addStatus('Courier Created');

        return $courier;
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = Courier::query();

        if (! empty($search)) {
            $query->where('courier_number', 'like', '%'.$search.'%');
        }

        // apply filters
        FilterFactory::apply($query, $filters);

        $records = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => CourierCollection::collection($records),
            'meta' => [
                'total' => $records->total(),
                'current_page' => $records->currentPage(),
                'perPage' => $records->perPage(),
                'lastPage' => $records->lastPage(),
            ],
        ]);

    }

    public function deleteCourier(Courier $courier)
    {
        DeleteCourier::run($courier);
    }

    public function changeStatus(array $courierData, string $status)
    {
        foreach ($courierData as $selectedCourier) {
            $courier = GetCourierByCourierNumber::run($selectedCourier[2]);
            UpdateCourierStatus::run($courier, $status);
        }
    }

    public function updateCourier(Courier $courier, $data)
    {
        $courier = UpdateCourier::run($courier, $data);
        $packagesData = $data['packages'];
        UpdateCourierPackages::run($courier, $packagesData);

        return $courier;
    }
}
