<?php

namespace App\Http\Controllers;

use App\Actions\User\GetUserCurrentBranchID;
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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly RoleRepositoryInterface $roleRepository,
        private readonly BranchRepositoryInterface $branchRepository,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('users.list');

        return Inertia::render('User/UserList', [
            'roles' => $this->roleRepository->getRoles(),
            'branches' => $this->branchRepository->getUserBranches(),
            'userRole' => Auth()->user()->getRoleNames()[0],
            'currentBranch' => GetUserCurrentBranchID::run(),
            'isSuperAdmin' => Auth::user()->name === 'Super Administrator' && Auth::user()->hasRole('admin'),
        ]);
    }

    public function list(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $query = User::withoutRole(['customer', 'driver'])
            ->currentBranch()
            ->with('branches');

        if (! empty($search)) {
            $query->where('username', 'like', '%'.$search.'%');
        }

        $users = $query->orderBy($order, $dir)->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'data' => UserCollection::collection($users),
            'meta' => [
                'total' => $users->total(),
                'current_page' => $users->currentPage(),
                'perPage' => $users->perPage(),
                'lastPage' => $users->lastPage(),
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
        $this->authorize('users.edit');

        return Inertia::render('User/EditUser', [
            'userRecord' => $user->load('roles', 'branches'),
            'roles' => $this->roleRepository->getRoles(),
            'branches' => $this->branchRepository->getUserBranches(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        if ($user->hasRole('admin') || $user->hasRole('super-admin')) {
            // Prevent non-super-admins from editing admin/super-admin users
            if (auth()->user() && ! auth()->user()->hasRole('super-admin')) {
                abort(403, 'You are not authorized to modify this user.');
            }
        }

        $this->userRepository->updateUser($request->all(), $user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->hasRole('admin') || $user->hasRole('super-admin')) {
            // Prevent non-super-admins from deleting admin/super-admin users
            if (auth()->user() && ! auth()->user()->hasRole('super-admin')) {
                abort(403, 'You are not authorized to delete this user.');
            }
        }

        $this->authorize('users.delete');

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

    public function export()
    {
        return $this->userRepository->export();
    }
}
