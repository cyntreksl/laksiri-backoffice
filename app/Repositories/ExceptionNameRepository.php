<?php

namespace App\Repositories;

use App\Actions\ExceptionName\CreateExceptionName;
use App\Actions\ExceptionName\DeleteExceptionName;
use App\Actions\ExceptionName\GetExceptionNames;
use App\Actions\ExceptionName\UpdateExceptionName;
use App\Interfaces\ExceptionNameRepositoryInterface;
use App\Models\ExceptionName;

class ExceptionNameRepository implements ExceptionNameRepositoryInterface
{
    public function getExceptionNames()
    {
        try {
            return GetExceptionNames::run();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get exception names: '.$e->getMessage());
        }
    }

    public function storeExceptionName(array $data)
    {
        try {
            return CreateExceptionName::run($data);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create exception name: '.$e->getMessage());
        }
    }

    public function updateExceptionName(array $data, ExceptionName $exceptionName)
    {
        try {
            return UpdateExceptionName::run($data, $exceptionName);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update exception name: '.$e->getMessage());
        }
    }

    public function destroyExceptionName(ExceptionName $exceptionName)
    {
        try {
            return DeleteExceptionName::run($exceptionName);
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete exception name: '.$e->getMessage());
        }
    }
}
