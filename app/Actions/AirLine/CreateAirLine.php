<?php

namespace App\Actions\AirLine;

use App\Models\AirLine;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateAirLine
{
    use AsAction;

    public function handle(array $data): AirLine
    {
        return AirLine::create([
            'name' => $data['name'],
            'branch_id' => session('current_branch_id'),
            'created_by' => Auth::user()->id,
        ]);
    }
}
