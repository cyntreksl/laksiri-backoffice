<?php

namespace App\Repositories;

use App\Http\Resources\TokenResource;
use App\Interfaces\GridJsInterface;
use App\Interfaces\TokenRepositoryInterface;
use App\Models\Token;

class TokenRepository implements GridJsInterface, TokenRepositoryInterface
{
    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = Token::query();

        if (! empty($search)) {
            $query->where('token', 'like', '%'.$search.'%');
        }

        // apply filters
        //        FilterFactory::apply($query, $filters);

        $records = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => TokenResource::collection($records),
            'meta' => [
                'total' => $records->total(),
                'current_page' => $records->currentPage(),
                'perPage' => $records->perPage(),
                'lastPage' => $records->lastPage(),
            ],
        ]);
    }
}
