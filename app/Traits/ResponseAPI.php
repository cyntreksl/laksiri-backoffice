<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ResponseAPI
{
    /**
     * Core of response
     */
    public function coreResponse(string $message, object|array|null $data, int $statusCode, bool $isSuccess = true): JsonResponse
    {
        // Check the params
        if (! $message) {
            return response()->json(['message' => 'Message is required'], 500);
        }

        // Send the response
        if ($isSuccess) {
            return response()->json([
                'message' => $message,
                'error' => false,
                'code' => $statusCode,
                'results' => $data,
            ], $statusCode);
        } else {
            return response()->json([
                'message' => $message,
                'errors' => $data,
                'error' => true,
                'code' => $statusCode,
            ], $statusCode);
        }
    }

    /**
     * Send any success response
     */
    public function success(string $message, object|array $data, int $statusCode = 200): JsonResponse
    {
        return $this->coreResponse($message, $data, $statusCode);
    }

    /**
     * Send any error response
     */
    public function error(string $message, object|array|null $data = null, int $statusCode = 500): JsonResponse
    {
        return $this->coreResponse($message, $data, $statusCode, false);
    }
}
