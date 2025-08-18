<?php

namespace App\Support;

use Spatie\Permission\Models\Role;

class RoleHierarchy
{
    /**
     * Compare two hierarchies using the rule:
     * current_role_hierarchy <= target_role_hierarchy
     */
    public static function canActOn(Role|float|int $actor, Role|float|int $target): bool
    {
        $actorHierarchy = is_numeric($actor) ? (float) $actor : (float) ($actor->hierarchy ?? 100.00);
        $targetHierarchy = is_numeric($target) ? (float) $target : (float) ($target->hierarchy ?? 100.00);

        return $actorHierarchy <= $targetHierarchy;
    }
}
