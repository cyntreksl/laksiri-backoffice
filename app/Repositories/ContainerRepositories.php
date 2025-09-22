<?php

namespace App\Repositories;

use App\Actions\Container\CreateContainer;
use App\Actions\Container\GetContainerByReference;
use App\Actions\Container\Loading\GetLoadedContainerById;
use App\Actions\Container\Loading\GetLoadedContainers;
use App\Actions\Container\MarkAsReached;
use App\Actions\Container\MarkAsRTF;
use App\Actions\Container\MarkAsUnRTF;
use App\Actions\Container\Unloading\CreateDraftUnload;
use App\Actions\Container\Unloading\CreateFullyUnload;
use App\Actions\Container\Unloading\UndoUnloadContainer;
use App\Actions\Container\Unloading\UnloadHBL;
use App\Actions\Container\Unloading\UnloadHBLPackages;
use App\Actions\Container\UpdateContainer;
use App\Actions\Container\UpdateContainerStatus;
use App\Actions\Container\UpdateContainerSystemStatus;
use App\Actions\ContainerDocument\DeleteDocument;
use App\Actions\ContainerDocument\DownloadDocument;
use App\Actions\ContainerDocument\UploadDocument;
use App\Actions\HBL\UpdateHBLSystemStatus;
use App\Actions\MHBL\GetMHBLById;
use App\Actions\Setting\GetSettings;
use App\Actions\UnloadingIssue\CreateUnloadingIssue;
use App\Actions\UnloadingIssue\UploadUnloadingIssueImages;
use App\Actions\UnloadingIssueImages\DeleteUnloadingIssueFile;
use App\Actions\UnloadingIssueImages\DownloadSingleUnloadingIssueFile;
use App\Actions\UnloadingIssueImages\GetUnloadingIssueImages;
use App\Actions\User\GetUserCurrentBranchID;
use App\Actions\VesselSchedule\GetVesselSchedule;
use App\Enum\ContainerStatus;
use App\Exports\ContainersExport;
use App\Exports\LoadedShipmentsExport;
use App\Exports\ShipmentArrivalsExport;
use App\Factory\Container\FilterFactory;
use App\Http\Resources\ContainerResource;
use App\Interfaces\ContainerRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\Container;
use App\Models\ContainerDocument;
use App\Models\HBL;
use App\Models\Scopes\BranchScope;
use App\Models\UnloadingIssue;
use App\Models\UnloadingIssueFile;
use App\Services\ContainerWeightService;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ContainerRepositories implements ContainerRepositoryInterface, GridJsInterface
{
    /**
     * @throws \Exception
     */
    public function store(array $data): Container
    {
        try {
            $container = CreateContainer::run($data);

            if ($data['vessel_schedule_id']) {
                $vesselSchedule = GetVesselSchedule::run($data['vessel_schedule_id']);

                $vesselSchedule->scheduleContainers()->create([
                    'container_id' => $container->id,
                ]);
            }

            return $container;
        } catch (\Exception $e) {
            throw new \Exception('Failed to create container: '.$e->getMessage());
        }
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = Container::query()->whereIn('status', [ContainerStatus::DRAFT->value, ContainerStatus::REQUESTED->value]);

        if (! empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('container_number', 'like', '%'.$search.'%')
                    ->orWhere('bl_number', 'like', '%'.$search.'%')
                    ->orWhere('awb_number', 'like', '%'.$search.'%');
            });
        }

        FilterFactory::apply($query, $filters);

        $containers = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => ContainerResource::collection($containers),
            'meta' => [
                'total' => $containers->total(),
                'current_page' => $containers->currentPage(),
                'perPage' => $containers->perPage(),
                'lastPage' => $containers->lastPage(),
            ],
        ]);
    }

    public function unloadHBLFromContainer(array $data, Container $container)
    {
        try {
            DB::beginTransaction();

            UnloadHBL::run($data, $container);

            if (! $container->hbl_packages()->exists()) {
                UpdateContainerStatus::run($container, ContainerStatus::REQUESTED->value);
            }

            ContainerWeightService::recalculate($container);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to unload hbl from container: '.$e->getMessage());
        }
    }

    public function unloadMHBLFromContainer(array $data, Container $container)
    {
        try {
            DB::beginTransaction();

            $mhblsHBL = GetMHBLById::run($data['mhbl_id'])->hbls;

            foreach ($mhblsHBL as $hbl) {
                $hblData = [
                    'hbl_id' => $hbl->id,
                ];
                UnloadHBL::run($hblData, $container);

                if (! $container->hbl_packages()->exists()) {
                    UpdateContainerStatus::run($container, ContainerStatus::REQUESTED->value);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to unload hbl from container: '.$e->getMessage());
        }
    }

    public function batchHBLDownload(Container $container): BinaryFileResponse
    {
        // Define the PDF directory
        $pdfDirectory = public_path('pdf/');

        // Ensure the PDF directory exists and clean it
        if (! File::exists($pdfDirectory)) {
            File::makeDirectory($pdfDirectory, 0755, true);
        } else {
            $pdfFile = new Filesystem;
            $pdfFile->cleanDirectory($pdfDirectory);
        }

        $container = GetLoadedContainerById::run($container);

        // Initialize a new Dompdf instance with custom options
        $options = new Options;
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        // Create an empty string to store the combined HTML
        $combinedHtml = '';

        $settings = GetSettings::run();

        $logoBase64 = null;

        if ($settings && ! empty($settings->logo)) {
            try {
                if (Storage::disk('s3')->exists($settings->logo)) {
                    $logoContent = Storage::disk('s3')->get($settings->logo);
                    $mime = Storage::disk('s3')->mimeType($settings->logo);

                    $logoBase64 = 'data:'.$mime.';base64,'.base64_encode($logoContent);
                }
            } catch (\Exception $e) {
                Log::warning('Unable to access logo file: '.$e->getMessage());
            }
        }

        foreach ($container->hbls as $hbl) {
            // Render each HBL as HTML and append to combinedHtml
            $combinedHtml .= view('pdf.hbls.hbl', [
                'hbl' => $hbl,
                'settings' => GetSettings::run(),
                'logoPath' => $logoBase64,
            ])->render();
            //            $combinedHtml .= '<div style="page-break-after: always;"></div>'; // Add page break after each HBL
        }

        // Load the combined HTML into Dompdf
        $dompdf->loadHtml($combinedHtml);

        // Set paper size and orientation if needed
        $dompdf->setPaper('A4', 'portrait');

        // Render the PDF
        $dompdf->render();

        // Define the final PDF file path
        $finalPdfPath = $pdfDirectory.$container->reference.'_combined_'.date('Y_m_d_h_i_s').'.pdf';

        // Save the generated PDF to a file
        file_put_contents($finalPdfPath, $dompdf->output());

        // Return the combined PDF as a download response
        return response()->download($finalPdfPath)->deleteFileAfterSend(true);
    }

    public function batchMHBLDownload(Container $container): BinaryFileResponse
    {
        // Define the PDF directory
        $pdfDirectory = public_path('pdf/');

        // Ensure the PDF directory exists and clean it
        if (! File::exists($pdfDirectory)) {
            File::makeDirectory($pdfDirectory, 0755, true);
        } else {
            $pdfFile = new Filesystem;
            $pdfFile->cleanDirectory($pdfDirectory);
        }

        $container = GetLoadedContainerById::run($container);

        // Get all MHBLs from the container
        $mhbls = collect($container->hbls)
            ->filter(function ($hbl) {
                return $hbl->mhbl !== null;
            })
            ->groupBy('mhbl.id')
            ->map(function ($hbls) {
                return $hbls->first()->mhbl;
            })
            ->values();

        if ($mhbls->isEmpty()) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('No MHBLs found in this container.');
        }

        // Initialize a new Dompdf instance with custom options
        $options = new Options;
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        // Create an empty string to store the combined HTML
        $combinedHtml = '';

        $settings = GetSettings::run();

        $logoBase64 = null;

        if ($settings && ! empty($settings->logo)) {
            try {
                if (Storage::disk('s3')->exists($settings->logo)) {
                    $logoContent = Storage::disk('s3')->get($settings->logo);
                    $mime = Storage::disk('s3')->mimeType($settings->logo);

                    $logoBase64 = 'data:'.$mime.';base64,'.base64_encode($logoContent);
                }
            } catch (\Exception $e) {
                Log::warning('Unable to access logo file: '.$e->getMessage());
            }
        }

        foreach ($mhbls as $mhbl) {
            // Get all HBLs for this MHBL
            $hbls = $mhbl->hbls;

            // Get all HBLs with their packages
            $hblsWithPackages = $hbls->map(function ($hbl) {
                return \App\Actions\HBL\GetHBLByIdWithPackages::run($hbl->id);
            });

            // Build a flat package list from all HBLs
            $packages = collect($hblsWithPackages)->flatMap(function ($hbl) {
                return $this->mapPackagesWithHblData($hbl);
            })->values();

            $hblsCollection = collect($hblsWithPackages);

            $summary = [
                'total_packages' => $packages->count(),
                'total_quantity' => $packages->sum('quantity'),
                'freight_charge' => $hblsCollection->sum('freight_charge'),
                'destination_charge' => $hblsCollection->sum('destination_charge'),
                'bill_charge' => $hblsCollection->sum('bill_charge'),
                'grand_total' => $hblsCollection->sum('grand_total'),
                'paid_amount' => $hblsCollection->sum('paid_amount'),
                'total_volume' => $packages->sum('volume'),
                'total_weight' => $packages->sum('actual_weight'),
            ];

            // Render each MHBL as HTML and append to combinedHtml
            $combinedHtml .= view('pdf.mhbl.hbl', [
                'mhbl' => $mhbl,
                'packages' => $packages,
                'settings' => $settings,
                'logoPath' => $logoBase64,
                'summary' => $summary,
            ])->render();
        }

        // Load the combined HTML into Dompdf
        $dompdf->loadHtml($combinedHtml);

        // Set paper size and orientation if needed
        $dompdf->setPaper('A4', 'portrait');

        // Render the PDF
        $dompdf->render();

        // Define the final PDF file path
        $finalPdfPath = $pdfDirectory.$container->reference.'_mhbl_combined_'.date('Y_m_d_h_i_s').'.pdf';

        // Save the generated PDF to a file
        file_put_contents($finalPdfPath, $dompdf->output());

        // Return the combined PDF as a download response
        return response()->download($finalPdfPath)->deleteFileAfterSend(true);
    }

    /**
     * Map packages with HBL data
     *
     * @param array|App\Models\HBL $hbl
     * @return array
     */
    private function mapPackagesWithHblData(array|HBL $hbl): array
    {
        // Convert HBL model to array if needed
        if ($hbl instanceof HBL) {
            $hbl = $hbl->toArray();
            if (isset($hbl['packages']) && $hbl['packages'] instanceof \Illuminate\Database\Eloquent\Collection) {
                $hbl['packages'] = $hbl['packages']->toArray();
            }
        }

        if (! isset($hbl['packages']) || ! is_iterable($hbl['packages'])) {
            return [];
        }

        return collect($hbl['packages'])->map(function ($package) use ($hbl) {
            return array_merge(
                is_array($package) ? $package : $package->toArray(),
                [
                    'hbl_number' => $hbl['hbl_number'] ?? '',
                    'consignee_name' => $hbl['consignee_name'] ?? '',
                    'consignee_address' => $hbl['consignee_address'] ?? '',
                    'consignee_contact' => $hbl['consignee_contact'] ?? '',
                    'consignee_nic' => $hbl['consignee_nic'] ?? '',
                    'shipper_name' => $hbl['hbl_name'] ?? '',
                    'shipper_address' => $hbl['address'] ?? '',
                    'shipper_contact' => $hbl['contact_number'] ?? '',
                    'shipper_nic' => $hbl['nic'] ?? '',
                ]
            );
        })->all();
    }

    public function deleteLoading(Container $container)
    {
        try {
            DB::beginTransaction();

            $hbls = $container
                ->hbl_packages
                ->pluck('hbl')
                ->unique();

            foreach ($hbls as $hbl) {
                UpdateHBLSystemStatus::run($hbl, HBL::SYSTEM_STATUS_CASH_RECEIVED, "HBL {$hbl->reference} has been deleted from container {$container->reference}");
            }

            UnloadHBLPackages::run($container);

            $container->addStatus('Shipment Deleted', 'Shipment has been deleted');

            UpdateContainerStatus::run($container, ContainerStatus::REQUESTED->value);

            $container->addStatus('Shipment Re-Ordered', 'Shipment has been re-ordered');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to delete loaded shipment: '.$e->getMessage());
        }
    }

    public function update(array $data, Container $container)
    {
        try {
            $data['is_reached'] = isset($data['is_reached']) ? ($data['is_reached'] ? 1 : 0) : 0;

            UpdateContainer::run($container, $data);

            if ($data['is_reached']) {
                foreach ($container->hbl_packages as $package) {
                    $hbl = HBL::withoutGlobalScope(BranchScope::class)->find($package->hbl_id);

                    $hbl->addStatus('Container Arrival', $container->estimated_time_of_arrival);
                }
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to update loaded shipment container: '.$e->getMessage());
        }
    }

    public function getLoadedContainers()
    {
        return GetLoadedContainers::run();
    }

    public function unloadContainer(array $data)
    {
        try {
            if (isset($data['is_draft'])) {
                return CreateDraftUnload::run($data);
            } else {
                return CreateFullyUnload::run($data);
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to unload container: '.$e->getMessage());
        }
    }

    public function reloadContainer(array $data)
    {
        try {
            return UndoUnloadContainer::run($data);
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete draft unloaded: '.$e->getMessage());
        }
    }

    public function createUnloadingIssue(array $data): void
    {
        CreateUnloadingIssue::run($data);
        UploadUnloadingIssueImages::run($data);
    }

    public function markAsReached($containerId)
    {
        $container = Container::query()
            ->withoutGlobalScope(BranchScope::class)
            ->where('id', $containerId)
            ->with([
                'hbl_packages' => function ($query) {
                    $query->withoutGlobalScope(BranchScope::class)->with([
                        'hbl' => function ($query) {
                            $query->withoutGlobalScope(BranchScope::class);
                        },
                    ]);
                },
            ])
            ->first();

        $hbls = $container->hbl_packages->pluck('hbl');

        try {
            foreach ($hbls as $hbl) {
                $hbl->status = 'reached';
                $hbl->save();
            }
            MarkAsReached::run($container);
        } catch (\Exception $e) {
            throw new \Exception('Failed to mark as reached container: '.$e->getMessage());
        }
    }

    public function export(array $filters)
    {
        return Excel::download(new ContainersExport($filters), 'containers.xlsx');
    }

    public function exportLoadedShipments(array $filters)
    {
        return Excel::download(new LoadedShipmentsExport($filters), 'loaded-shipments.xlsx');
    }

    public function exportShipmentArrivals(array $filters)
    {
        return Excel::download(new ShipmentArrivalsExport($filters), 'shipment-arrivals.xlsx');
    }

    public function uploadDocument(array $data)
    {
        try {
            UploadDocument::run($data);
        } catch (\Exception $e) {
            throw new \Exception('Failed to upload container document: '.$e->getMessage());
        }
    }

    public function deleteDocument(ContainerDocument $containerDocument): void
    {
        try {
            DeleteDocument::run($containerDocument);
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete container document: '.$e->getMessage());
        }
    }

    public function getContainerByHBL(HBL $hbl)
    {
        $package = $hbl->packages()
            ->whereHas('duplicate_containers') // Ensures only packages with containers are included
            ->with('duplicate_containers') // Eager loads the containers
            ->first();

        $containerDetails = $package ? $package->duplicate_containers->flatten()->first() : [];

        return response()->json($containerDetails);
    }

    public function downloadDocument(ContainerDocument $container_document)
    {
        return DownloadDocument::run($container_document);
    }

    public function downloadUnloadingIssueImages(UnloadingIssue $unloadingIssue)
    {

        return GetUnloadingIssueImages::run($unloadingIssue);
    }

    public function deleteUnloadingIssueFile(UnloadingIssueFile $unloadingIssueFile)
    {
        try {
            return DeleteUnloadingIssueFile::run($unloadingIssueFile);
        } catch (\Exception $exception) {
            throw new \Exception('Failed to delete file: '.$exception->getMessage());
        }
    }

    public function downloadSingleUnloadingIssueFile(string $id)
    {
        try {
            return DownloadSingleUnloadingIssueFile::run($id);
        } catch (\Exception $exception) {
            throw new \Exception('Failed to download file: '.$exception->getMessage());
        }
    }

    public function getContainerByReference(string $reference, string|int $vesselScheduleId)
    {
        try {
            return GetContainerByReference::run($reference, $vesselScheduleId);
        } catch (\Exception $exception) {
            throw new \Exception('Failed to getting container: '.$exception->getMessage());
        }
    }

    public function getAfterDispatchShipmentsList(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = []): JsonResponse
    {
        $query = Container::query()->whereIn('status', [
            ContainerStatus::REACHED_DESTINATION->value,
            //            ContainerStatus::ARRIVED_PRIMARY_WAREHOUSE->value,
        ])->withoutGlobalScope(BranchScope::class);

        if (! empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('container_number', 'like', '%'.$search.'%')
                    ->orWhere('bl_number', 'like', '%'.$search.'%')
                    ->orWhere('awb_number', 'like', '%'.$search.'%');
            });
        }

        $query->where('target_warehouse', GetUserCurrentBranchID::run());

        FilterFactory::apply($query, $filters);

        $containers = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => ContainerResource::collection($containers),
            'meta' => [
                'total' => $containers->total(),
                'current_page' => $containers->currentPage(),
                'perPage' => $containers->perPage(),
                'lastPage' => $containers->lastPage(),
            ],
        ]);
    }

    public function updateInboundShipmentStatus(Container $container)
    {
        try {
            $hbls = $container
                ->hbl_packages
                ->pluck('hbl')
                ->unique();

            foreach ($hbls as $hbl) {
                $hbl->is_arrived_to_primary_warehouse = true;
                $hbl->save();

                $hbl->addStatus('HBL Arrived to Primary Warehouse');
            }

            UpdateContainer::run($container, [
                'arrived_at_primary_warehouse' => now(),
                'arrived_primary_warehouse_by' => auth()->id(),
            ]);

            UpdateContainerStatus::run($container, ContainerStatus::ARRIVED_PRIMARY_WAREHOUSE->value);

            UpdateContainerSystemStatus::run($container, Container::SYSTEM_STATUS_CONTAINER_PRIMARY_WAREHOUSE_ARRIVAL);

            $container->addStatus('Container Arrived to Primary Warehouse', 'Container has been arrived to primary warehouse');
        } catch (\Exception $e) {
            throw new \Exception('Failed to mark as arrived to warehouse: '.$e->getMessage());
        }
    }

    public function getAfterInboundShipmentsList(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = []): JsonResponse
    {
        $query = Container::query()->whereIn('status', [
            //            ContainerStatus::ARRIVED_PRIMARY_WAREHOUSE->value,
            ContainerStatus::UNLOADED->value,
            //            ContainerStatus::DEPARTED_PRIMARY_WAREHOUSE->value,
        ])->withoutGlobalScope(BranchScope::class);

        if (! empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('container_number', 'like', '%'.$search.'%')
                    ->orWhere('bl_number', 'like', '%'.$search.'%')
                    ->orWhere('awb_number', 'like', '%'.$search.'%');
            });
        }

        FilterFactory::apply($query, $filters);
        $query->where('target_warehouse', GetUserCurrentBranchID::run());
        $containers = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => ContainerResource::collection($containers),
            'meta' => [
                'total' => $containers->total(),
                'current_page' => $containers->currentPage(),
                'perPage' => $containers->perPage(),
                'lastPage' => $containers->lastPage(),
            ],
        ]);
    }

    public function updateOutboundShipmentStatus(Container $container)
    {
        try {
            UpdateContainer::run($container, [
                'departed_at_primary_warehouse' => now(),
                'departed_primary_warehouse_by' => auth()->id(),
            ]);

            UpdateContainerStatus::run($container, ContainerStatus::DEPARTED_PRIMARY_WAREHOUSE->value);

            UpdateContainerSystemStatus::run($container, Container::SYSTEM_STATUS_CONTAINER_PRIMARY_WAREHOUSE_DEPART);

            $container->addStatus('Container Departed from Primary Warehouse', 'Container has been departed from primary warehouse');
        } catch (\Exception $e) {
            throw new \Exception('Failed to mark as departed from warehouse: '.$e->getMessage());
        }
    }

    public function doRTF(Container $container): void
    {
        try {
            MarkAsRTF::run($container);

            $hbls = $container
                ->hbl_packages
                ->pluck('hbl')
                ->unique();

            foreach ($hbls as $hbl) {
                \App\Actions\HBL\MarkAsRTF::run($hbl);
            }

            $packages = $container
                ->hbl_packages
                ->unique();

            foreach ($packages as $package) {
                \App\Actions\HBL\HBLPackage\MarkAsRTF::run($package);
            }

        } catch (\Exception $e) {
            throw new \Exception('Failed to mark as rtf container: '.$e->getMessage());
        }
    }

    public function undoRTF(Container $container): void
    {
        try {
            MarkAsUnRTF::run($container);

            $hbls = $container
                ->hbl_packages
                ->pluck('hbl')
                ->unique();

            foreach ($hbls as $hbl) {
                \App\Actions\HBL\MarkAsUnRTF::run($hbl);
            }

            $packages = $container
                ->hbl_packages
                ->unique();

            foreach ($packages as $package) {
                \App\Actions\HBL\HBLPackage\MarkAsUnRTF::run($package);
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to undo rtf container: '.$e->getMessage());
        }
    }

    public function getAllShipmentsList(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = []): JsonResponse
    {
        $query = Container::query();

        if (! empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('container_number', 'like', '%'.$search.'%')
                    ->orWhere('bl_number', 'like', '%'.$search.'%')
                    ->orWhere('awb_number', 'like', '%'.$search.'%');
            });
        }

        FilterFactory::apply($query, $filters);

        $containers = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => ContainerResource::collection($containers),
            'meta' => [
                'total' => $containers->total(),
                'current_page' => $containers->currentPage(),
                'perPage' => $containers->perPage(),
                'lastPage' => $containers->lastPage(),
            ],
        ]);
    }
}
