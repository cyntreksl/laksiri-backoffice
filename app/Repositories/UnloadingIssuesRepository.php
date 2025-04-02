<?php

namespace App\Repositories;

use App\Factory\UnloadingIssue\FilterFactory;
use App\Http\Resources\UnloadingIssueResource;
use App\Interfaces\GridJsInterface;
use App\Interfaces\UnloadingIssuesRepositoryInterface;
use App\Models\HBL;
use App\Models\Scopes\BranchScope;
use App\Models\UnloadingIssue;
use Illuminate\Http\JsonResponse;

class UnloadingIssuesRepository implements GridJsInterface, UnloadingIssuesRepositoryInterface
{
    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = UnloadingIssue::query();

        $query->with('hblPackage', function ($q) {
            $q->withoutGlobalScope(BranchScope::class)->with('hbl', function ($q) {
                $q->withoutGlobalScope(BranchScope::class);
            });
        });

        if (! empty($search)) {
            $query->whereAny([
                'hbl',
                'hbl_name',
                'consignee_name',
            ], 'like', '%'.$search.'%');
        }

        // apply filters
        FilterFactory::apply($query, $filters);

        $records = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => UnloadingIssueResource::collection($records),
            'meta' => [
                'total' => $records->total(),
                'current_page' => $records->currentPage(),
                'perPage' => $records->perPage(),
                'lastPage' => $records->lastPage(),
            ],
        ]);
    }

    public function getUnloadingIssuesByHbl(HBL $hbl): JsonResponse
    {
        $hbl = HBL::with('unloadingIssues.hblPackage')->findOrFail($hbl->id);

        return response()->json($hbl->unloadingIssues()->with('hblPackage')->get());
    }
}
