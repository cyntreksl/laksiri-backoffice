<?php

namespace App\Repositories;

use App\Actions\CourierAgent\CreateCourierAgent;
use App\Actions\CourierAgent\GetCourierAgent;
use App\Actions\CourierAgent\UpdateCourierAgent;
use App\Interfaces\CourierAgentRepositoryInterface;

class CourierAgentRepository  implements CourierAgentRepositoryInterface
{
    public function getAllCourierAgents()
    {
        return GetCourierAgent::run();
    }

    public function storeCourierAgent(array $data)
    {
        try {
            return CreateCourierAgent::run($data);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create Courier Agent: '.$e->getMessage());
        }
    }

    public function updateCourierAgent(array $data, $id)
    {
        try {
            return UpdateCourierAgent::run($data, $id);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update Courier Agent: '.$e->getMessage());
        }
    }

}
