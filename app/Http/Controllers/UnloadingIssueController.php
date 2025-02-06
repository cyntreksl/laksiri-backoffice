<?php

namespace App\Http\Controllers;

use App\Interfaces\ContainerRepositoryInterface;
use App\Interfaces\UnloadingIssuesRepositoryInterface;
use App\Models\HBL;
use App\Models\UnloadingIssue;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnloadingIssueController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly UnloadingIssuesRepositoryInterface $unloadingIssuesRepository,
        private readonly ContainerRepositoryInterface $ContainerRepository,
    ) {
    }

    public function index()
    {
        $this->authorize('issues.index');

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

    public function getUnloadingIssuesByHbl(HBL $hbl)
    {
        return $this->unloadingIssuesRepository->getUnloadingIssuesByHbl($hbl);
    }

    public function getUnloadingIssuesImage(unloadingIssue $unloadingIssue)
    {

        return $this->ContainerRepository->downloadUnloadingIssueImages($unloadingIssue);
    }
}
