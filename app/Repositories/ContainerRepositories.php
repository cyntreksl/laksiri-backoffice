<?php

namespace App\Repositories;

use App\Actions\Container\CreateContainer;
use App\Actions\Container\Loading\GetLoadedContainerById;
use App\Actions\Container\Loading\GetLoadedContainers;
use App\Actions\Container\MarkAsReached;
use App\Actions\Container\Unloading\CreateDraftUnload;
use App\Actions\Container\Unloading\CreateFullyUnload;
use App\Actions\Container\Unloading\UndoUnloadContainer;
use App\Actions\Container\Unloading\UnloadHBL;
use App\Actions\Container\Unloading\UnloadHBLPackages;
use App\Actions\Container\UpdateContainer;
use App\Actions\Container\UpdateContainerStatus;
use App\Actions\ContainerDocument\DeleteDocument;
use App\Actions\ContainerDocument\DownloadDocument;
use App\Actions\ContainerDocument\UploadDocument;
use App\Actions\MHBL\GetMHBLById;
use App\Actions\Setting\GetSettings;
use App\Actions\UnloadingIssue\CreateUnloadingIssue;
use App\Actions\UnloadingIssue\UploadUnloadingIssueImages;
use App\Actions\UnloadingIssueImages\DeleteUnloadingIssueFile;
use App\Actions\UnloadingIssueImages\DownloadSingleUnloadingIssueFile;
use App\Actions\UnloadingIssueImages\GetUnloadingIssueImages;
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
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

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

        // apply filters
        FilterFactory::apply($query, $filters);

        $countQuery = $query;
        $totalRecords = $countQuery->count();

        $containers = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

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
        try {
            DB::beginTransaction();

            UnloadHBL::run($data, $container);

            if (! $container->hbl_packages()->exists()) {
                UpdateContainerStatus::run($container, ContainerStatus::REQUESTED->value);
            }

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

    //    public function abandonedBatchHBLDownload(Container $container)
    //    {
    //        // Define the PDF and ZIP directories
    //        $pdfDirectory = public_path('pdf/');
    //        $zipDirectory = public_path('zip/');
    //
    //        // Ensure the PDF directory exists and clean it
    //        if (! File::exists($pdfDirectory)) {
    //            File::makeDirectory($pdfDirectory, 0755, true);
    //        } else {
    //            $pdfFile = new Filesystem();
    //            $pdfFile->cleanDirectory($pdfDirectory);
    //        }
    //
    //        // Ensure the ZIP directory exists
    //        if (! File::exists($zipDirectory)) {
    //            File::makeDirectory($zipDirectory, 0755, true);
    //        }
    //
    //        $container = GetLoadedContainerById::run($container);
    //
    //        foreach ($container->hbls as $hbl) {
    //            // create random filename for pdfs
    //            $filename = $container->reference.'_'.date('Y_m_d_h_i_s').'_'.Str::random(10);
    //            // save pdfs
    //            PDF::loadView('pdf.hbls.hbl', [
    //                'hbl' => $hbl,
    //            ])->save($pdfDirectory.$filename.'.pdf');
    //        }
    //
    //        // create new ZipArchive instance
    //        $zip = new ZipArchive;
    //        // creating file name for zip archive file
    //        $zip_filename = $container->reference.'_'.date('Y_m_d_h_i_s').'.zip';
    //        $zipPath = $zipDirectory.$zip_filename;
    //
    //        if (File::exists($zipPath)) {
    //            File::delete($zipPath);
    //        }
    //
    //        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
    //
    //            $files = File::files($pdfDirectory);
    //
    //            foreach ($files as $value) {
    //                $relativeNameInZipFile = basename($value);
    //                $zip->addFile($value, $relativeNameInZipFile);
    //            }
    //
    //            $zip->close();
    //        }
    //
    //        return response()->download($zipPath);
    //    }

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

        foreach ($container->hbls as $hbl) {
            // Render each HBL as HTML and append to combinedHtml
            $combinedHtml .= view('pdf.hbls.hbl', ['hbl' => $hbl, 'settings' => GetSettings::run(), 'logoPath' => GetSettings::run()['logo_url'] ?? null])->render();
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

    public function deleteLoading(Container $container)
    {
        try {
            DB::beginTransaction();

            UnloadHBLPackages::run($container);

            UpdateContainerStatus::run($container, ContainerStatus::REQUESTED->value);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to delete loaded shipment: '.$e->getMessage());
        }
    }

    public function update(array $data, Container $container)
    {
        try {
            $data['is_reached'] = $data['is_reached'] ? 1 : 0;
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
}
