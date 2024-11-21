<?php

namespace App\Actions\Container\Loading;

use App\Actions\Container\UpdateContainer;
use App\Actions\Container\UpdateContainerStatus;
use App\Actions\Container\UpdateReferenceNumber;
use App\Actions\HBL\HBLPackage\MarkAsLoaded;
use App\Actions\User\GetUserCurrentBranch;
use App\Enum\ContainerStatus;
use App\Models\Container;
use App\Models\HBL;
use App\Models\HBLPackage;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateLoadedContainer
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(array $data)
    {
        try {
            DB::beginTransaction();

            $container = Container::find($data['container_id']);

            foreach ($data['packages'] as $package) {
                $result = $container->hbl_packages()->updateExistingPivot($package['id'], [
                    'status' => 'loaded',
                    'loaded_by' => auth()->id(),
                ]);

                if (! $result) {
                    $container->hbl_packages()->attach($package['id'], [
                        'status' => 'loaded',
                        'loaded_by' => auth()->id(),
                    ]);
                }

                // Run the MarkAsLoaded action for the package ID
                MarkAsLoaded::run($package['id']);

                $hbl_package = HBLPackage::find($package['id']);
                $hbl = HBL::find($hbl_package->hbl_id);

                $hbl->addStatus('Container Shipped');
            }

            UpdateContainerStatus::run($container, ContainerStatus::LOADED->value);

            $reference = GenerateLoadingReferenceNumber::run(GetUserCurrentBranch::run()['branchCode']);

            UpdateReferenceNumber::run($container, $reference);

            $container->addStatus('Container Loaded');

            $container->addStatus('Container Shipped');

            // update container loading end datetime and who loaded by
            $data = [
                'loading_ended_at' => now(),
                'loading_ended_by' => auth()->id(),
                'note' => $data['note'] ?? null,
            ];

            UpdateContainer::run($container, $data);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to create loaded container: '.$e->getMessage());
        }
    }
}
