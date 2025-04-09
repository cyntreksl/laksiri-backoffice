<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaxRequest;
use App\Http\Requests\UpdateTaxRequest;
use App\Interfaces\TaxRepositoryInterface;
use App\Models\Tax;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaxController extends Controller
{
    public function __construct(
        private readonly TaxRepositoryInterface $taxRepository,
    ) {}

    public function index()
    {
        return Inertia::render('Setting/Tax/TaxList');
    }

    public function list(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);
        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'drivers', 'officers', 'paymentStatus', 'deliveryType', 'warehouse']);

        return $this->taxRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function store(StoreTaxRequest $request)
    {
        $this->taxRepository->createTax($request->all());
    }

    public function update(Tax $tax, UpdateTaxRequest $request)
    {
        $this->taxRepository->updateTax($tax, $request->all());
    }

    public function destroy(Tax $tax)
    {
        $this->taxRepository->destroyTax($tax);
    }
}
