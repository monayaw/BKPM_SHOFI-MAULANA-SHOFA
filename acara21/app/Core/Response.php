
<?php

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
            'errors' => (object) $errors,
            'timestamp' => date('c'),
        ];

        // JSON_PRETTY_PRINT hanya digunakan saat development
        $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

        if (defined('APP_ENV') && APP_ENV === 'development') {
            $flags |= JSON_PRETTY_PRINT;
        }

        echo json_encode($response, $flags);
        exit;
    }
}
