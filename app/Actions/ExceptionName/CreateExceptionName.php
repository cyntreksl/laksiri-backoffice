<?php

namespace App\Actions\ExceptionName;

use App\Models\ExceptionName;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateExceptionName
{
    use AsAction;

    public function handle(array $data): ExceptionName
    {
        return ExceptionName::create($data);
    }
}
