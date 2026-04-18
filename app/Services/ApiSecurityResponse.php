<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;

class ApiSecurityResponse
{
    /**
     * Build a standardized JSON security error response.
     */
    public static function error(
        string $message,
        int $status = 400,
        string $code = 'security_request_rejected',
        array $details = [],
        array $headers = []
    ): JsonResponse {
        $payload = [
            'message' => $message,
            'error' => [
                'code' => $code,
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        if (! empty($details)) {
            $payload['error']['details'] = $details;
        }

        $response = response()->json($payload, $status);

        foreach ($headers as $name => $value) {
            $response->headers->set($name, (string) $value);
        }

        return $response;
    }
}