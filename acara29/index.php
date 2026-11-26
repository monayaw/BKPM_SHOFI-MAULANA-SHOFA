<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$logDir = __DIR__ . '/storage/logs';
$logFile = $logDir . '/php-error.log';

if (!is_dir($logDir)) {
    mkdir($logDir, 0775, true);
}

ini_set('log_errors', '1');
ini_set('display_errors', '0');
ini_set('error_log', $logFile);

require_once __DIR__ . '/app/Core/autoload.php';

try {
    $router = new Router();
    $router->dispatch();
} catch (Throwable $e) {
    error_log(
        date('Y-m-d H:i:s') . ' | ' . get_class($e) . ' | '
        . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine()
    );

    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan pada server.',
        'data' => null,
        'errors' => new stdClass(),
        'timestamp' => date('c'),
    ]);
}
