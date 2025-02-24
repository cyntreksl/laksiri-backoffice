<?php

namespace App\Interfaces;

interface CourierAgentRepositoryInterface
{
    public function getAllCourierAgents();

    public function storeCourierAgent(array $data);

    public function updateCourierAgent(array $data, $id);

    public function destroyCourierAgent($id);

}
