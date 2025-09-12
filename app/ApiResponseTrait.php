<?php

namespace App;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Generate a consistent API response for both success and error.
     *
     * @param  mixed  $data
     */
    public function sendApiResponse(bool $status, string $message, $data = null, int $statusCode = 200): JsonResponse
    {
        $response = [
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ];

        return response()->json($response, $statusCode);
    }
}
