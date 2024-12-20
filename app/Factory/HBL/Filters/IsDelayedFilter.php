<?php

namespace App\Factory\HBL\Filters;

use App\Actions\Branch\GetBranchById;
use App\Interfaces\FilterInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class IsDelayedFilter implements FilterInterface
{
    public function apply(Builder $query, $value)
    {
        $delayDates = GetBranchById::run(session('current_branch_id'))['container_delays'];

        if ((bool) $value) {
            return $query->whereHas('packages.containers', function ($query) {
                $query->whereRaw('DATE(reached_date) < ?', [Carbon::now()->subDays(15)->toDateString()]);
            })->with(['packages.containers']);
        }

        return $query;
    }
}
