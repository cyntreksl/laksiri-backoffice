<?php

namespace App\Http\Controllers;

use App\Interfaces\CustomerRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
    ) {}

    public function index()
    {
        return Inertia::render('Customer/CustomerList');
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate']);

        return $this->customerRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }
}
