<?php

namespace App\Actions\ExceptionName;

use App\Models\ExceptionName;
use Lorisleiva\Actions\Concerns\AsAction;

class GetExceptionNames
{
    use AsAction;

    public function handle()
    {
        return ExceptionName::all();
    }
}
