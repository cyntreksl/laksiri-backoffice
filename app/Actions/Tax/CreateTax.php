<?php

namespace App\Actions\Tax;

use App\Models\Tax;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateTax
{
    use AsAction;

    public function handle(array $data): Tax
    {
        return Tax::create([
            'name' => $data['name'],
            'rate' => $data['rate'],
            'is_active' => $data['is_active'],
            'branch_id' => session('current_branch_id'),
            'created_by' => Auth::user()->id,
        ]);
    }
}
