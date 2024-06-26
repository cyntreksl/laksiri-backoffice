<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Interfaces\RoleRepositoryInterface;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleRepositoryInterface $roleRepository,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Roles/RoleList', [
            'roles' => $this->roleRepository->getRoles(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Roles/CreateRole', [
            'allPermissions' => $this->roleRepository->getPermissions(),
            'permissionGroups' => $this->roleRepository->getPermissionGroups(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        return $this->roleRepository->storeRole($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        return Inertia::render('Roles/EditRole', [
            'allPermissions' => $this->roleRepository->getPermissions(),
            'permissionGroups' => $this->roleRepository->getPermissionGroups(),
            'role' => $role->load('permissions'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        return $this->roleRepository->updateRole($request->all(), $role);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        return $this->roleRepository->deleteRole($role);
    }

    public function getPermissionByGroupName($groupName)
    {
        return $this->roleRepository->getPermissionsByGroupName($groupName);
    }
}
