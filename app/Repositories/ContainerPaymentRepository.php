<?php

namespace App\Repositories;

use App\Actions\Container\GetContainerPayment;
use App\Actions\ContainerPayment\CreateContainerPayment;
use App\Actions\ContainerPayment\DeleteContainerPayment;
use App\Factory\ContainerPayment\FilterFactory;
use App\Http\Resources\ContainerPaymentResource;
use App\Interfaces\ContainerPaymentRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\Container;
use App\Models\ContainerPayment;
use Illuminate\Http\JsonResponse;

class ContainerPaymentRepository implements ContainerPaymentRepositoryInterface, GridJsInterface
{
    public function getContainerPayment(Container $container)
    {
        return GetContainerPayment::run($container);
    }

    public function store(array $data): ContainerPayment
    {
        try {
            return CreateContainerPayment::run($data);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create container: '.$e->getMessage());
        }
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = []): JsonResponse
    {
        $query = ContainerPayment::query()->where(function ($query) {
            $query->where('is_finance_approved', '=', '0');
        });

        if (! empty($search)) {
            $query->whereHas('container', function ($q) use ($search) {
                $q->where('reference', 'like', '%'.$search.'%');
            });
        }

        $container_payments = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        // apply filters
        FilterFactory::apply($query, $filters);

        return response()->json([
            'data' => ContainerPaymentResource::collection($container_payments),
            'meta' => [
                'total' => $container_payments->total(),
                'current_page' => $container_payments->currentPage(),
                'perPage' => $container_payments->perPage(),
                'lastPage' => $container_payments->lastPage(),
            ],
        ]);
    }

    public function delete(ContainerPayment $containerPayment)
    {
        return DeleteContainerPayment::run($containerPayment);
    }
}
