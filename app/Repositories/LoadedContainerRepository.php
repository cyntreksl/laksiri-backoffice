<?php

namespace App\Repositories;

use App\Actions\Container\Loading\CreateDraftLoadedContainer;
use App\Actions\Container\Loading\CreateOrUpdateLoadedContainer;
use App\Actions\Container\Loading\DeleteDraftLoadedContainer;
use App\Enum\ContainerStatus;
use App\Exports\LoadedContainerManifestExport;
use App\Factory\Container\FilterFactory;
use App\Http\Resources\ContainerResource;
use App\Interfaces\GridJsInterface;
use App\Interfaces\LoadedContainerRepositoryInterface;
use App\Models\Container;
use App\Models\ContainerDocument;
use App\Models\Scopes\BranchScope;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    public function deleteDraft(array $data)
    {
        try {
            return DeleteDraftLoadedContainer::run($data);
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete draft loaded container: '.$e->getMessage());
        }
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        if (request()->header('referer') === route('arrival.shipments-arrivals.index')) {
            $query = Container::query()->whereIn('status', [
                ContainerStatus::IN_TRANSIT->value,
                ContainerStatus::REACHED_DESTINATION->value,
                ContainerStatus::LOADED->value,
            ])->withoutGlobalScope(BranchScope::class);
        } else {
            $query = Container::query()->whereIn('status', [
                ContainerStatus::IN_TRANSIT->value,
                ContainerStatus::REACHED_DESTINATION->value,
                ContainerStatus::LOADED->value,
            ]);
        }

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
        $totalRecords = $countQuery->count();

        $loaded_containers = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

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

    public function downloadManifestFile($container): BinaryFileResponse
    {
        // generate file name
        $filename = $container->reference.'_manifest_'.date('Y_m_d_h_i_s').'.xlsx';

        return Excel::download(new LoadedContainerManifestExport($container), $filename);
    }
    public function updateVerificationStatus (array $data)
    {

        $document = ContainerDocument::where('container_id', $data['containerId'])->first();
        $value = $data['isChecked'] == true ? '1' : '0';
        $document->update(['is_verified' => $value]);


        return $document;
    }

}
