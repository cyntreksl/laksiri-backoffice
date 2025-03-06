<?php

namespace App\Repositories;

use App\Actions\CourierAgent\CreateCourierAgent;
use App\Actions\CourierAgent\DeleteCourierAgent;
use App\Actions\CourierAgent\GetCourierAgent;
use App\Actions\CourierAgent\UpdateCourierAgent;
use App\Factory\User\FilterFactory;
use App\Http\Resources\CourierAgentCollection;
use App\Interfaces\CourierAgentRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\CourierAgent;

class CourierAgentRepository implements CourierAgentRepositoryInterface, GridJsInterface
{
    public function getAllCourierAgents()
    {
        return GetCourierAgent::run();
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = CourierAgent::query();

        if ($search) {
            $query->where('company_name', 'like', '%'.$search.'%');
            $query->orWhere('website', 'like', '%'.$search.'%');

        }
        // apply filters
        FilterFactory::apply($query, $filters);
        $countQuery = $query;
        $totalRecords = $countQuery->count();

        $users = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

        return response()->json([
            'data' => CourierAgentCollection::collection($users),
            'meta' => [
                'total' => $totalRecords,
                'page' => $offset,
                'perPage' => $limit,
                'lastPage' => ceil($totalRecords / $limit),
            ],
        ]);

    }

    public function storeCourierAgent(array $data)
    {

        try {
            $courierAgent = CreateCourierAgent::run($data);
            if (isset($data['logo'])) {
                $courierAgent->updateFile($data['logo'], 'logo', 'courier_agents/logo');
            }

            return $courierAgent;

        } catch (\Exception $e) {
            throw new \Exception('Failed to create Courier Agent: '.$e->getMessage());
        }
    }

    public function updateCourierAgent(array $data, $id)
    {
        try {
            $courierAgent = UpdateCourierAgent::run($data, $id);

            if (isset($data['logo'])) {
                $courierAgent->updateFile($data['logo'], 'logo', 'courier_agents/logo');
            }

            return $courierAgent;

        } catch (\Exception $e) {
            throw new \Exception('Failed to update Courier Agent: '.$e->getMessage());
        }
    }

    public function destroyCourierAgent($id)
    {
        try {
            return DeleteCourierAgent::run($id);
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete Courier Agent: '.$e->getMessage());
        }
    }
}
