<?php

namespace App\Repositories;

use App\Actions\Container\CreateContainer;
use App\Actions\Container\Unloading\UnloadHBL;
use App\Enum\ContainerStatus;
use App\Factory\Container\FilterFactory;
use App\Http\Resources\ContainerResource;
use App\Interfaces\ContainerRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\Container;

class ContainerRepositories implements ContainerRepositoryInterface, GridJsInterface
{
    /**
     * @throws \Exception
     */
    public function store(array $data): Container
    {
        try {
            return CreateContainer::run($data);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create container: '.$e->getMessage());
        }
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = Container::query()->where('status', '<>', ContainerStatus::LOADED->value);

        if (! empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('container_number', 'like', '%'.$search.'%')
                    ->orWhere('bl_number', 'like', '%'.$search.'%')
                    ->orWhere('awb_number', 'like', '%'.$search.'%');
            });
        }

        //apply filters
        FilterFactory::apply($query, $filters);

        $countQuery = $query;

        $containers = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

        $totalRecords = $countQuery->count();

        return response()->json([
            'data' => ContainerResource::collection($containers),
            'meta' => [
                'total' => $totalRecords,
                'page' => $offset,
                'perPage' => $limit,
                'lastPage' => ceil($totalRecords / $limit),
            ],
        ]);
    }

    public function unloadHBLFromContainer(array $data, Container $container)
    {
        return UnloadHBL::run($data, $container);
    }
}
