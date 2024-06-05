<?php

namespace App\Actions\Loading\LoadedContainer;

use App\Actions\Container\UpdateContainerStatus;
use App\Actions\HBL\HBLPackage\MarkAsLoaded;
use App\Actions\User\GetUserCurrentBranch;
use App\Actions\User\GetUserCurrentBranchID;
use App\Enum\ContainerStatus;
use App\Models\LoadedContainer;
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

            $reference = GenerateLoadingReferenceNumber::run(GetUserCurrentBranch::run()['branchName']);

            foreach ($data['packages'] as $package) {
                // Find the loaded container by its unique identifiers or create a new instance
                $loaded_container = LoadedContainer::firstOrNew([
                    'hbl_package_id' => $package['id'],
                ]);

                // Set the common attributes
                $loaded_container->branch_id = GetUserCurrentBranchID::run();
                $loaded_container->container_id = $data['container_id'];
                $loaded_container->hbl_id = $package['hbl_id'];
                $loaded_container->note = $data['note'] ?? null;
                $loaded_container->cargo_type = $data['cargo_type'];
                $loaded_container->status = 'Loaded';
                $loaded_container->loaded_by = auth()->id();

                // If the loaded container already exists (i.e., is not a new instance), set is_draft to false
                if ($loaded_container->exists) {
                    $loaded_container->is_draft = false;
                }

                // Set reference only if the container is new
                if (! $loaded_container->exists) {
                    $loaded_container->reference = $reference;

                    // Run the MarkAsLoaded action for the package ID
                    MarkAsLoaded::run($package['id']);
                }

                // Save the loaded container
                $loaded_container->save();
            }

            UpdateContainerStatus::run($data['container_id'], ContainerStatus::CONTAINER_LOADED->value);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to create loaded container: '.$e->getMessage());
        }
    }
}
