<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Interfaces\Api\ExceptionNameRepositoryInterface;

class ExceptionNameController extends Controller
{
    public function __construct(
        private readonly ExceptionNameRepositoryInterface $exceptionNameRepository,
    ) {}

    /**
     * Get exception names
     *
     * Display the exception name list.
     *
     * @group Exception Names
     */
    public function index()
    {
        return $this->exceptionNameRepository->getExceptionNames();
    }
}
