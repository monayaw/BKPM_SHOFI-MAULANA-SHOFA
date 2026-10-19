<?php

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {

            $config = require __DIR__ . '/../../config/database.php';

            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";

            try {

                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );

            } catch (PDOException $e) {

                // Lokasi folder logging
                $logDir = __DIR__ . '/../../storage/logs';
                $logFile = $logDir . '/app.log';

                // Membuat folder logs jika belum ada
                if (!is_dir($logDir)) {
                    mkdir($logDir, 0750, true);
                }

                // Isi log
                $message = sprintf(
                    "[%s] %s: %s in %s:%d%s",
                    date('Y-m-d H:i:s'),
                    get_class($e),
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine(),
                    PHP_EOL
                );

                // Menyimpan error ke app.log
                file_put_contents(
                    $logFile,
                    $message,
                    FILE_APPEND
                );

                // Jangan tampilkan detail database ke user
                throw new Exception(
                    'Terjadi kesalahan pada koneksi database.'
                );
            }
        }

        return self::$instance;
    }
}