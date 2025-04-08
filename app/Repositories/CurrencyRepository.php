<?php

namespace App\Repositories;

use App\Actions\CurrencyRate\CreateCurrencyRate;
use App\Actions\CurrencyRate\DeleteCurrencyRate;
use App\Actions\CurrencyRate\UpdateCurrenciesRates;
use App\Actions\CurrencyRate\UpdateCurrencyRate;
use App\Http\Resources\CurrencyRateResourse;
use App\Interfaces\CurrencyRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\Currency;
use Illuminate\Http\JsonResponse;

class CurrencyRepository implements CurrencyRepositoryInterface, GridJsInterface
{
    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = []): JsonResponse
    {
        $query = Currency::query();

        if (! empty($search)) {
            $query->whereAny([
                'currency_name',
                'currency_symbol',
            ], 'like', '%'.$search.'%');
        }

        $currencies = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => CurrencyRateResourse::collection($currencies),
            'meta' => [
                'total' => $currencies->total(),
                'current_page' => $currencies->currentPage(),
                'perPage' => $currencies->perPage(),
                'lastPage' => $currencies->lastPage(),
            ],
        ]);
    }

    public function createCurrency(array $data)
    {
        return CreateCurrencyRate::run($data);
    }

    public function updateCurrency(Currency $currency, array $data)
    {
        try {
            return UpdateCurrencyRate::run($currency, $data);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update currency: '.$e->getMessage());
        }
    }

    public function destroyCurrency(Currency $currency)
    {
        try {
            return DeleteCurrencyRate::run($currency);
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete currency: '.$e->getMessage());
        }
    }

    public function updateCurrencyRate(array $currencyIds, float $sl_rate)
    {
        try {
            return UpdateCurrenciesRates::run($currencyIds, $sl_rate);
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete currency: '.$e->getMessage());
        }
    }
}
