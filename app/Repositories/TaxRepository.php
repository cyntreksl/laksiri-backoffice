<?php

namespace App\Repositories;

use App\Actions\Tax\CreateTax;
use App\Actions\Tax\DeleteTax;
use App\Actions\Tax\UpdateTax;
use App\Http\Resources\TaxCollection;
use App\Interfaces\GridJsInterface;
use App\Interfaces\TaxRepositoryInterface;
use App\Models\Tax;

class TaxRepository implements GridJsInterface, TaxRepositoryInterface
{
    public function createTax(array $data): Tax
    {
        return CreateTax::run($data);
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = Tax::query();
        if (! empty($search)) {
            $query->where('name', 'like', '%'.$search.'%');
        }
        $taxes = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => TaxCollection::collection($taxes),
            'meta' => [
                'total' => $taxes->total(),
                'current_page' => $taxes->currentPage(),
                'perPage' => $taxes->perPage(),
                'lastPage' => $taxes->lastPage(),
            ],
        ]);
    }

    public function updateTax(Tax $tax, array $data)
    {
        try {
            return UpdateTax::run($tax, $data);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update tax: '.$e->getMessage());
        }
    }

    public function destroyTax(Tax $tax)
    {
        try {
            return DeleteTax::run($tax);
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete tax: '.$e->getMessage());
        }
    }
}
