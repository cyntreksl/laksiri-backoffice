<?php

namespace App\Actions\CourierAgent;

use App\Models\CourierAgent;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;
class GetCourierAgent
{
    use AsAction;


    public function handle(): Collection|array
    {
        return CourierAgent::all();
    }

}
