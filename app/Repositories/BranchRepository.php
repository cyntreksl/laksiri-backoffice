<?php

namespace App\Repositories;

use App\Actions\Branch\CreateAgent;
use App\Actions\Branch\CreateBranch;
use App\Actions\Branch\GetAgent;
use App\Actions\Branch\GetBranches;
use App\Actions\Branch\GetDepartureBranches;
use App\Actions\Branch\GetDestinationBranches;
use App\Actions\Branch\GetUserBranches;
use App\Actions\Branch\UpdateAgent;
use App\Actions\Branch\UpdateBranch;
use App\Factory\User\FilterFactory;
use App\Http\Resources\AgentCollection;
use App\Interfaces\BranchRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\Branch;

class BranchRepository implements BranchRepositoryInterface, GridJsInterface
{
    public function getBranches()
    {
        return GetBranches::run();
    }

    public function getDestinationBranches()
    {
        return GetDestinationBranches::run();
    }

    public function getDepartureBranches()
    {
        return GetDepartureBranches::run();
    }

    public function createBranch(array $data)
    {
        return CreateBranch::run($data);
    }

    public function updateBranch(array $data, Branch $branch)
    {
        return UpdateBranch::run($data, $branch);
    }

    public function getUserBranches()
    {
        return GetUserBranches::run();
    }

    public function getBranchesByType()
    {
        return GetAgent::run();
    }

    public function createAgent(array $data)
    {

        return CreateAgent::run($data);
    }

    public function updateAgent(array $data, Branch $branch)
    {

        return UpdateAgent::run($data, $branch);
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = Branch::query();

        if (! empty($search)) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $query->where('is_third_party_agent', true);

        // apply filters
        FilterFactory::apply($query, $filters);

        $countQuery = $query;
        $totalRecords = $countQuery->count();

        $users = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

        return response()->json([
            'data' => AgentCollection::collection($users),
            'meta' => [
                'total' => $totalRecords,
                'page' => $offset,
                'perPage' => $limit,
                'lastPage' => ceil($totalRecords / $limit),
            ],
        ]);

    }
}
