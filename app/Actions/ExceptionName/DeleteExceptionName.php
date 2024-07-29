<?php

namespace App\Actions\ExceptionName;

use App\Models\ExceptionName;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteExceptionName
{
    use AsAction;

    public function handle(ExceptionName $exceptionName): void
    {
        $exceptionName->delete();
    }
}
