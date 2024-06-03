<?php

namespace App\Actions\Loading\LoadedContainer;

use App\Actions\User\GetUserCurrentBranch;
use App\Actions\User\GetUserCurrentBranchID;
use App\Models\LoadedContainer;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateDraftLoadedContainer
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(array $data)
    {
        try {
            $reference = GenerateLoadingReferenceNumber::run(GetUserCurrentBranch::run()['branchName']);

            foreach ($data['packages'] as $package) {
                $loaded_container = new LoadedContainer();
                $loaded_container->branch_id = GetUserCurrentBranchID::run();
                $loaded_container->container_id = $data['container_id'];
                $loaded_container->hbl_id = $package['hbl_id'];
                $loaded_container->hbl_package_id = $package['id'];
                $loaded_container->reference = $reference;
                $loaded_container->cargo_type = $data['cargo_type'];
                $loaded_container->status = 'Pending';
                $loaded_container->loaded_by = auth()->id();
                $loaded_container->is_draft = true;
                $loaded_container->save();
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to create draft loaded container: '.$e->getMessage());
        }
    }
}
