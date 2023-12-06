<?php

namespace App\Traits;

trait APIResponse
{
    public function successResponse(
        string $message,
        array|object $data = null,
        int $status_code = 200,
        bool $success = true,
    ) {
        return $this->responseAPI(
            message: $message,
            data: $data,
            status_code: $status_code,
            success: $success,
        );
    }

    public function failedResponse(
        string $message,
        array|object $data = null,
        int $status_code = 500,
        bool $success = false
    ) {
        return $this->responseAPI(
            message: $message,
            data: $data,
            status_code: $status_code,
            success: $success
        );
    }

    private function responseAPI(
        string $message,
        array|object $data = null,
        int $status_code,
        bool $success = true
    ) {
        // check the parameters
        if (!$message) {
            return response()->json([
                'message' => 'message is required'
            ], 500);
        }

        // send the response
        $response_body = [
            'message' => $message,
            'code' => $status_code,
        ];

        if ($success) {
            $response_body['results'] = $data;
        }

        return response()->json($response_body, $status_code);
    }
}
