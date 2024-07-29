<?php

namespace App\Actions\ExceptionName;

use App\Models\ExceptionName;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateExceptionName
{
    use AsAction;

    public function handle(array $data, ExceptionName $exceptionName)
    {
        $exceptionName->update($data);
    }
}
