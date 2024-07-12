<?php

namespace App\Http\Controllers;

use App\Interfaces\UnloadingIssuesRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnloadingIssueController extends Controller
{
    public function __construct(
        private readonly UnloadingIssuesRepositoryInterface $unloadingIssuesRepository,
    ) {
    }

    public function index()
    {
        return Inertia::render('Arrival/UnloadingIssueList');
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate']);

        return $this->unloadingIssuesRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }
}
