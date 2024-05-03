<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserCollection;
use App\Interfaces\BranchRepositoryInterface;
use App\Interfaces\RoleRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface   $userRepository,
        private readonly RoleRepositoryInterface   $roleRepository,
        private readonly BranchRepositoryInterface $branchRepository,
    )
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('User/UserList', [
            'roles' => $this->roleRepository->getRoles(),
            'branches' => $this->branchRepository->getBranches(),
        ]);
    }

    public function list(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('offset', 1);
        $order = $request->input('order', 'id');
        $dir = $request->input('dir', 'asc');
        $search = $request->input('search', null);

        $query = User::with('branches');

        if (!empty($search)) {
            $query->where('username', 'like', '%' . $search . '%');
        }

        $users = $query->orderBy($order, $dir)
            ->skip($page)
            ->take($limit)
            ->get();

        $totalUsers = User::count();

        return response()->json([
            'data' => UserCollection::collection($users),
            'meta' => [
                'total' => $totalUsers,
                'page' => $page,
                'perPage' => $limit,
                'lastPage' => ceil($totalUsers / $limit)
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $this->userRepository->storeUser($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->userRepository->deleteUser($user);
    }
}
