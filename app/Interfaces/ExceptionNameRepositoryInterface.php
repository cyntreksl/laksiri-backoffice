<?php

namespace App\Interfaces;

use App\Models\ExceptionName;

interface ExceptionNameRepositoryInterface
{
    public function getExceptionNames();

    public function storeExceptionName(array $data);

    public function updateExceptionName(array $data, ExceptionName $exceptionName);

    public function destroyExceptionName(ExceptionName $exceptionName);
}
