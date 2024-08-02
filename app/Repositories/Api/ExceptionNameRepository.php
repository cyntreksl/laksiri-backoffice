<?php

namespace App\Repositories\Api;

use App\Actions\ExceptionName\GetExceptionNames;
use App\Interfaces\Api\ExceptionNameRepositoryInterface;
use App\Traits\ResponseAPI;
use Illuminate\Http\JsonResponse;

class ExceptionNameRepository implements ExceptionNameRepositoryInterface
{
    use ResponseAPI;

    public function getExceptionNames(): JsonResponse
    {
        try {
            $exception_names = GetExceptionNames::run();

            return $this->success('Exception name list received successfully!', $exception_names);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }
}
