
<?php

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            // Memuat konfigurasi database
            $config = require __DIR__ . '/../../config/database.php';

            // Membuat koneksi ke database MySQL
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";

            self::$connection = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }

        return self::$connection;
    }
}
