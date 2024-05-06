<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDriverRequest;
use App\Http\Resources\DriverCollection;
use App\Interfaces\DriverRepositoryInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverController extends Controller
{
    public function __construct(
        private readonly DriverRepositoryInterface $driverRepository,
    ) {
    }

    public function index()
    {
        return Inertia::render('Driver/DriverList');
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);

        $query = User::currentBranch()->role('driver');

        if (! empty($search)) {
            $query->where('username', 'like', '%'.$search.'%');
        }

        $users = $query->orderBy($order, $dir)
            ->skip($page)
            ->take($limit)
            ->get();

        $totalUsers = User::currentBranch()->count();

        return response()->json([
            'data' => DriverCollection::collection($users),
            'meta' => [
                'total' => $totalUsers,
                'page' => $page,
                'perPage' => $limit,
                'lastPage' => ceil($totalUsers / $limit),
            ],
        ]);
    }

    public function store(StoreDriverRequest $request)
    {
        $this->driverRepository->storeDriver($request->all());
    }

    public function edit(User $user)
    {
    }

    public function update(Request $request, User $user)
    {
    }

    public function destroy(User $user)
    {
    }
}
