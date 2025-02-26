<?php

namespace App\Actions\Branch;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GetAgent
{
    use AsAction;

    public function handle(string $searchQuery = ''): Collection
    {
        $query = Branch::where('is_third_party_agent', true);

        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', '%' . $searchQuery . '%')
                    ->orWhere('branch_code', 'like', '%' . $searchQuery . '%')
                    ->orWhere('currency_name', 'like', '%' . $searchQuery . '%')
                    ->orWhere('currency_symbol', 'like', '%' . $searchQuery . '%');
            });
        }

        return $query->get();
    }
}
