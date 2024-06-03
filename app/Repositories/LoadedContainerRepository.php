<?php

namespace App\Repositories;

use App\Actions\Loading\LoadedContainer\CreateLoadedContainer;
use App\Interfaces\LoadedContainerRepositoryInterface;

class LoadedContainerRepository implements LoadedContainerRepositoryInterface
{
    /**
     * @throws \Exception
     */
    public function store(array $data)
    {
        try {
            return CreateLoadedContainer::run($data);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create loaded container: '.$e->getMessage());
        }
    }
}
