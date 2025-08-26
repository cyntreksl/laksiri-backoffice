<?php

namespace App\Actions\Role;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\Permission\Models\Role;

class GetRoles
{
    use AsAction;

    public function handle(): Collection|array
    {
        $user = Auth::user();
        $actorHierarchy = $user?->roles?->first()?->hierarchy ?? null;

        return Role::query()
            // Restrict roles to those the current user can act on per hierarchy rule
            ->when($actorHierarchy !== null, function ($q) use ($actorHierarchy) {
                $q->where('hierarchy', '>=', $actorHierarchy);
            })
            ->with('permissions')
            ->orderBy('hierarchy') // Always sort by hierarchy
            ->get();
    }
}
