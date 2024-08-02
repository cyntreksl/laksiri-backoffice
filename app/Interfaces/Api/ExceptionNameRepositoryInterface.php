<?php

namespace App\Interfaces\Api;

use Illuminate\Http\JsonResponse;

interface ExceptionNameRepositoryInterface
{
    /**
     * Retrieve exception names.
     *
     * @method  GET api/v1/exception-names
     */
    public function getExceptionNames(): JsonResponse;
}
