<?php

namespace App\Repositories;

use App\Actions\Container\Loading\CreateDraftLoadedContainer;
use App\Actions\Container\Loading\CreateOrUpdateLoadedContainer;
use App\Actions\Container\Loading\DeleteDraftLoadedContainer;
use App\Actions\Container\Loading\GetLoadedContainerById;
use App\Actions\Setting\GetSettings;
use App\Enum\ContainerStatus;
use App\Exports\DoorToDoorManifestExport;
use App\Exports\LoadedContainerManifestExport;
use App\Factory\Container\FilterFactory;
use App\Http\Resources\ContainerResource;
use App\Interfaces\GridJsInterface;
use App\Interfaces\LoadedContainerRepositoryInterface;
use App\Models\Container;
use App\Models\ContainerDocument;
use App\Models\Scopes\BranchScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

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

        if (Auth::user()->hasRole('boned area')) {
            $query->where('target_warehouse', session('current_branch_id'));
        }

        if (! empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('container_number', 'like', '%'.$search.'%')
                    ->orWhere('bl_number', 'like', '%'.$search.'%')
                    ->orWhere('awb_number', 'like', '%'.$search.'%');
            });
        }

        // apply filters
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

    public function downloadManifestFile($container)
    {
        $filename = $container->reference.'_manifest_'.date('Y_m_d_h_i_s').'.pdf';

        $export = new LoadedContainerManifestExport($container);
        $settings = GetSettings::run();

        $data = array_filter($export->prepareData(), function ($item) {
            return isset($item[0]) && $item[0] !== '';
        });

        $cargoType = strtolower(trim($container->cargo_type));

        $view = ($cargoType === 'air cargo') ? 'exports.air_cargo' : 'exports.shipments';

        $pdf = PDF::loadView($view, ['data' => $data, 'container' => $container, 'settings' => $settings]);
        $pdf->setPaper('a3', 'portrait');

        return $pdf->download($filename);
    }

    public function updateVerificationStatus(array $data)
    {

        $document = ContainerDocument::find($data['containerId']);
        $document->update(['is_verified' => $data['isChecked']]);

        return $document;
    }

    public function downloadDoorToDoorPdf($container)
    {
        $filename = $container->reference.'_door_to_door_'.date('Y_m_d_h_i_s').'.pdf';

        $export = new DoorToDoorManifestExport($container);
        $settings = GetSettings::run();

        $data = array_filter($export->prepareData(), function ($item) {
            return isset($item[0]) && $item[0] !== '';
        });

        $groupedData = [];
        foreach ($data as $item) {
            if (isset($item[10]) && $item[10]) {
                $mhblKey = $item[10]->id;
                $groupedData[$mhblKey][] = $item;
            }
        }

        $pdf = PDF::loadView('exports.door_to_door', ['groupedData' => $groupedData, 'data' => $data, 'container' => $container, 'settings' => $settings]);
        $pdf->setPaper('a3', 'portrait');

        return $pdf->download($filename);

    }

    public function downloadUnloadingPointDoc($container)
    {
        $container = GetLoadedContainerById::run(Container::withoutGlobalScope(BranchScope::class)->findOrFail($container));
        $filename = $container->reference.'_Loading_Point_Document.pdf';
        $settings = GetSettings::run();

        $pdf = PDF::loadView('exports.loading-point-doc', ['container' => $container, 'hbls' => $container->hbls, 'settings' => $settings]);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}
