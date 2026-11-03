
<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Core/Response.php';
require_once __DIR__ . '/../app/Core/Auth.php';

// Konfigurasi pencatatan error
$logDir = dirname(__DIR__) . '/storage/logs';
$logFile = $logDir . '/php-error.log';

// Buat folder logs jika belum tersedia
if (!is_dir($logDir)) {
    if (!mkdir($logDir, 0775, true) && !is_dir($logDir)) {
        error_log('Gagal membuat folder log: ' . $logDir);
    }
}

// Aktifkan pencatatan error PHP
ini_set('log_errors', '1');
ini_set('display_errors', '0');
ini_set('error_log', $logFile);

// Buat file log jika folder tersedia tetapi file belum ada
if (is_dir($logDir) && !file_exists($logFile)) {
    if (file_put_contents($logFile, '') === false) {
        error_log('Gagal membuat file log: ' . $logFile);
    }
}

// Load model, controller, dan router
require_once __DIR__ . '/../app/Models/Mahasiswa.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Controllers/MahasiswaController.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Core/Router.php';

// Jalankan routing aplikasi
try {
    (new Router())->dispatch();
} catch (Throwable $e) {
    error_log(
        date('Y-m-d H:i:s')
        . ' | ' . get_class($e)
        . ' | ' . $e->getMessage()
        . ' | ' . $e->getFile()
        . ':' . $e->getLine()
    );

    http_response_code(500);

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan pada server.',
        'data' => null,
        'errors' => new stdClass(),
        'timestamp' => date('c')
    ]);
}
