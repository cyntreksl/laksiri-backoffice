<?php

namespace App\Actions\Container;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\Container;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateContainer
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(array $data): Container
    {
        try {
            $data['created_by'] = auth()->id();
            $data['branch_id'] = GetUserCurrentBranchID::run();
            $data['system_status'] = Container::SYSTEM_STATUS_BOOK_CONTAINER;

            $container = Container::create($data);

            $container->addStatus('Container Booked');

            return $container;
        } catch (\Exception $e) {
            throw new \Exception('Failed to create container: '.$e->getMessage());
        }
    }
}
