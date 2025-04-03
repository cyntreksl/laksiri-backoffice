<?php

namespace App\Repositories;

use App\Actions\AirLine\CreateAirLine;
use App\Actions\AirLine\DeleteAirLine;
use App\Actions\AirLine\UpdateAirLine;
use App\Http\Resources\AirLineResource;
use App\Interfaces\AirLineRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\AirLine;
use Illuminate\Http\JsonResponse;

class AirLineRepository implements AirLineRepositoryInterface, GridJsInterface
{
    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = []): JsonResponse
    {
        $query = AirLine::query();

        if (! empty($search)) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $air_lines = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => AirLineResource::collection($air_lines),
            'meta' => [
                'total' => $air_lines->total(),
                'current_page' => $air_lines->currentPage(),
                'perPage' => $air_lines->perPage(),
                'lastPage' => $air_lines->lastPage(),
            ],
        ]);
    }

    public function createAirLine(array $data)
    {
        return CreateAirLine::run($data);
    }

    public function updateAirLine(AirLine $airLine, array $data)
    {
        try {
            return UpdateAirLine::run($airLine, $data);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update air line: '.$e->getMessage());
        }
    }

    public function destroyAirLine(AirLine $airLine)
    {
        try {
            return DeleteAirLine::run($airLine);
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete air line: '.$e->getMessage());
        }
    }
}
