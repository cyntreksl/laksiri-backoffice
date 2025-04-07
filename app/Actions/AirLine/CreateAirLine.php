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
            'created_by' => Auth::user()->id,
        ]);
    }
}
