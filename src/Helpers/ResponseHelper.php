<?php

namespace App\Helpers;

use Psr\Http\Message\ResponseInterface as Response;

class ResponseHelper {
    public static function json(Response $response, string $status, int $statusCode, string $message, mixed $data = null, ?string $errorId = null): Response {
        $responseData = [
            'status' => $status,
            'status_code' => $statusCode,
            'message' => $message,
            'data' => $data
        ];

        if ($errorId !== null) {
            $responseData['error_id'] = $errorId;
        }

        $response->getBody()->write(json_encode($responseData));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($statusCode);
    }
}
