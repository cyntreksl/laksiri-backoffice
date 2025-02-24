<?php

namespace App\Repositories;

use App\Actions\CourierAgent\CreateCourierAgent;
use App\Actions\CourierAgent\GetCourierAgent;
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

}
