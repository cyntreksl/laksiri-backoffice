<?php

namespace App\Repositories;

use App\Actions\Container\CreateContainer;
use App\Actions\User\GetUserCurrentBranchID;
use App\Interfaces\ContainerRepositoryInterface;
use App\Models\Container;

class ContainerRepositories implements ContainerRepositoryInterface
{

    /**
     * @throws \Exception
     */
    public function store(array $data) :Container
    {
        try {
           return CreateContainer::run($data);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create container: ' . $e->getMessage());
        }
    }
}
