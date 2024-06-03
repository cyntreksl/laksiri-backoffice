<?php

namespace App\Repositories;

use App\Actions\Loading\LoadedContainer\CreateDraftLoadedContainer;
use App\Actions\Loading\LoadedContainer\CreateLoadedContainer;
use App\Actions\Loading\LoadedContainer\DeleteDraftLoadedContainer;
use App\Interfaces\LoadedContainerRepositoryInterface;
use App\Models\LoadedContainer;

class LoadedContainerRepository implements LoadedContainerRepositoryInterface
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
                return CreateLoadedContainer::run($data);
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
}
