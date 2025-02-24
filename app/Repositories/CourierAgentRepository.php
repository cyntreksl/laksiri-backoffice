<?php

namespace App\Repositories;

use App\Actions\CourierAgent\GetCourierAgent;
use App\Interfaces\CourierAgentRepositoryInterface;

class CourierAgentRepository  implements CourierAgentRepositoryInterface
{
    public function getAllCourierAgents()
    {
        return GetCourierAgent::run();
    }

}
