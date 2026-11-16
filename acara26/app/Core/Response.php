<?php

namespace App\Core;

use JsonException;

class Response
{
    public static function json(
        int $httpCode,
        bool $success,
        string $message,
        mixed $data = null,
        array $errors = []
    ): void {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');

        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
            'timestamp' => date('c'),
        ];
        $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR;
        if (defined('APP_ENV') && APP_ENV === 'development') {
            $flags |= JSON_PRETTY_PRINT;
        }

        try {
            echo json_encode($response, $flags);
        } catch (JsonException $exception) {
            error_log('JSON response encoding failed: ' . $exception->getMessage());
            http_response_code(500);
            echo '{"success":false,"message":"Response JSON gagal dibuat.","data":null,"errors":[],"timestamp":"'
                . gmdate('c')
                . '"}';
        }

        exit;
    }
}
