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
            if (! $data['container_type']) {
                $data['container_type'] = 'Custom';
            }
            $data['created_by'] = auth()->id();
            $data['branch_id'] = GetUserCurrentBranchID::run();
            $data['system_status'] = Container::SYSTEM_STATUS_BOOK_CONTAINER;

            $data['estimated_time_of_departure'] = (isset($data['estimated_time_of_departure']) && $data['estimated_time_of_departure'] === 'Invalid date') ? null : $data['estimated_time_of_departure'];
            $data['estimated_time_of_arrival'] = (isset($data['estimated_time_of_arrival']) && $data['estimated_time_of_arrival'] === 'Invalid date') ? null : $data['estimated_time_of_arrival'];

            $container = Container::create($data);

            $container->addStatus('Container Booked');

            return $container;
        } catch (\Exception $e) {
            throw new \Exception('Failed to create container: '.$e->getMessage());
        }
    }
}
