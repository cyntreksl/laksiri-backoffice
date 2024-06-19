<?php

namespace App\Repositories;

use App\Actions\Container\CreateContainer;
use App\Actions\Container\Loading\GetLoadedContainerById;
use App\Actions\Container\Unloading\UnloadHBL;
use App\Actions\Container\Unloading\UnloadHBLPackages;
use App\Actions\Container\UpdateContainerStatus;
use App\Enum\ContainerStatus;
use App\Factory\Container\FilterFactory;
use App\Http\Resources\ContainerResource;
use App\Interfaces\ContainerRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\Container;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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

    public function batchHBLDownload(Container $container)
    {
        // Define the PDF and ZIP directories
        $pdfDirectory = public_path('pdf/');
        $zipDirectory = public_path('zip/');

        // Ensure the PDF directory exists and clean it
        if (! File::exists($pdfDirectory)) {
            File::makeDirectory($pdfDirectory, 0755, true);
        } else {
            $pdfFile = new Filesystem();
            $pdfFile->cleanDirectory($pdfDirectory);
        }

        // Ensure the ZIP directory exists
        if (! File::exists($zipDirectory)) {
            File::makeDirectory($zipDirectory, 0755, true);
        }

        $container = GetLoadedContainerById::run($container);

        foreach ($container->hbls as $hbl) {
            // create random filename for pdfs
            $filename = $container->reference.'_'.date('Y_m_d_h_i_s').'_'.Str::random(10);
            // save pdfs
            PDF::loadView('pdf.hbls.hbl', [
                'hbl' => $hbl,
            ])->save($pdfDirectory.$filename.'.pdf');
        }

        // create new ZipArchive instance
        $zip = new ZipArchive;
        // creating file name for zip archive file
        $zip_filename = $container->reference.'_'.date('Y_m_d_h_i_s').'.zip';
        $zipPath = $zipDirectory.$zip_filename;

        if (File::exists($zipPath)) {
            File::delete($zipPath);
        }

        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {

            $files = File::files($pdfDirectory);

            foreach ($files as $value) {
                $relativeNameInZipFile = basename($value);
                $zip->addFile($value, $relativeNameInZipFile);
            }

            $zip->close();
        }

        return response()->download($zipPath);
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
}
