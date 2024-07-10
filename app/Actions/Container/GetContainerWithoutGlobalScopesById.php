<?php

namespace App\Actions\Container;

use App\Models\Container;
use App\Models\Scopes\BranchScope;
use Lorisleiva\Actions\Concerns\AsAction;

class GetContainerWithoutGlobalScopesById
{
    use AsAction;

    public function handle(string|int $container_id): Container
    {
        return Container::withoutGlobalScope(BranchScope::class)
            ->where('id', $container_id)
            ->with(['hbl_packages' => function ($query) {
                $query->withoutGlobalScopes([BranchScope::class])->with([
                    'hbl' => function ($query) {
                        $query->withoutGlobalScopes([BranchScope::class]);
                    },
                    'unloadingIssue',
                ]);
            }])
            ->first();
    }
}
