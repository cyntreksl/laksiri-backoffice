<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserBranchRequest;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserCollection;
use App\Interfaces\BranchRepositoryInterface;
use App\Interfaces\RoleRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly RoleRepositoryInterface $roleRepository,
        private readonly BranchRepositoryInterface $branchRepository,
    ) {
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

        $query = User::withoutRole('driver')
            ->currentBranch()
            ->with('branches');

        if (! empty($search)) {
            $query->where('username', 'like', '%'.$search.'%');
        }

        $users = $query->orderBy($order, $dir)
            ->skip($page)
            ->take($limit)
            ->get();

        $totalUsers = User::currentBranch()->count();

        return response()->json([
            'data' => UserCollection::collection($users),
            'meta' => [
                'total' => $totalUsers,
                'page' => $page,
                'perPage' => $limit,
                'lastPage' => ceil($totalUsers / $limit),
            ],
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
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {

        return Inertia::render('User/UserEdit', [
            'user' => $user->load('roles', 'branches'),
            'roles' => $this->roleRepository->getRoles(),
            'branches' => $this->branchRepository->getBranches(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->userRepository->updateUser($request->all(), $user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->userRepository->deleteUser($user);
    }

    public function changePassword(UpdateUserPasswordRequest $request, User $user)
    {
        $this->userRepository->updatePassword($request->all(), $user);
    }

    public function changeBranch(UpdateUserBranchRequest $request, User $user)
    {
        $this->userRepository->updateBranch($request->all(), $user);
    }

    public function switchBranch(Request $request): JsonResponse
    {
        $branchName = $request->input('branch_name');
        try {
            $response = $this->userRepository->switchBranch($branchName);

            return response()->json($response);
        } catch (Exception $exception) {
            Log::error('User branch switch failed: '.$exception->getMessage());

            return response()->json([]);
        }
    }
}
