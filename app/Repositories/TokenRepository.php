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
        $query = Token::query()->with(['customer', 'reception', 'hbl', 'queueLogs']);

        // Search functionality
        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('token', 'like', '%'.$search.'%')
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('hbl', function ($q) use ($search) {
                        $q->where('reference', 'like', '%'.$search.'%')
                            ->orWhere('hbl_number', 'like', '%'.$search.'%');
                    });
            });
        }

        // Date range filter
        if (! empty($filters['fromDate'])) {
            $query->whereDate('created_at', '>=', $filters['fromDate']);
        }

        if (! empty($filters['toDate'])) {
            $query->whereDate('created_at', '<=', $filters['toDate']);
        }

        // Status filter
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

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
