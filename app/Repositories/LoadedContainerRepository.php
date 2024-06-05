<?php

namespace App\Repositories;

use App\Actions\Loading\LoadedContainer\CreateDraftLoadedContainer;
use App\Actions\Loading\LoadedContainer\CreateOrUpdateLoadedContainer;
use App\Actions\Loading\LoadedContainer\DeleteDraftLoadedContainer;
use App\Factory\Container\FilterFactory;
use App\Http\Resources\ContainerResource;
use App\Interfaces\GridJsInterface;
use App\Interfaces\LoadedContainerRepositoryInterface;
use App\Models\Container;
use App\Models\LoadedContainer;

class LoadedContainerRepository implements GridJsInterface, LoadedContainerRepositoryInterface
{
    /**
     * @throws \Exception
     */
    public function store(array $data)
    {
        try {
            if (isset($data['is_draft'])) {
                return CreateDraftLoadedContainer::run($data);
            } else {
                return CreateOrUpdateLoadedContainer::run($data);
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to create loaded container: '.$e->getMessage());
        }
    }

    public function deleteDraft(string $hblPackageId)
    {
        try {
            $loadedContainer = LoadedContainer::where('hbl_package_id', $hblPackageId)->first();

            return DeleteDraftLoadedContainer::run($loadedContainer);
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete draft loaded container: '.$e->getMessage());
        }
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = Container::query()->loadedContainers();

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

        $loaded_containers = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

        $totalRecords = $countQuery->count();

        return response()->json([
            'data' => ContainerResource::collection($loaded_containers),
            'meta' => [
                'total' => $totalRecords,
                'page' => $offset,
                'perPage' => $limit,
                'lastPage' => ceil($totalRecords / $limit),
            ],
        ]);
    }
}
