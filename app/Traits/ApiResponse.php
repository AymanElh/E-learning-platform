<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Success response
     */
    protected function successResponse(string $message = null, $data = null, int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
        ];

        if($message != null) {
            $response['message'] = $message;
        }

        if($data != null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Error response
     */
    protected function errorResponse(string $message = null, array|object $errors = null, int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
        ];

        if($message != null) {
            $response['message'] = $message;
        }

        if($errors != null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * not found response
     */
    protected function notFoundResponse(string $message = "Resource not found"): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], 404);
    }


}
