<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCurrencyRateRequest;
use App\Http\Requests\UpdateCurrencyRateRequest;
use App\Interfaces\CurrencyRepositoryInterface;
use App\Models\Currency;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CurrencyRateController extends Controller
{
    public function __construct(
        private readonly CurrencyRepositoryInterface $currencyRepository,
    ) {}

    public function index()
    {
        return Inertia::render('Setting/Currencies/CurrenciesList');
    }

    public function list(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'drivers', 'officers', 'paymentStatus', 'deliveryType', 'warehouse']);

        return $this->currencyRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function store(StoreCurrencyRateRequest $request)
    {
        $this->currencyRepository->createCurrency($request->all());
    }

    public function update(Currency $currency, UpdateCurrencyRateRequest $request)
    {
        $this->currencyRepository->updateCurrency($currency, $request->all());
    }

    public function destroy(Currency $currency)
    {
        $this->currencyRepository->destroyCurrency($currency);
    }

    public function updateCurrencyRate(Request $request)
    {
        $this->currencyRepository->updateCurrencyRate($request->currency_ids, $request->sl_rate);
    }
}
