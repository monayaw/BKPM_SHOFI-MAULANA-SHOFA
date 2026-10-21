
<?php

class Router
{
    public function dispatch(): void
    {
        // Mengambil path URL yang diakses
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

        // Base URL aplikasi
        $base = '/WebServer/acara17/public';

        // Menghapus base URL dari path
        if (str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        // Membersihkan garis miring di awal dan akhir URL
        $uri = trim($uri, '/');

        // Endpoint mahasiswa
        if (
            $uri === '' ||
            $uri === 'mahasiswa' ||
            $uri === 'api/mahasiswa'
        ) {
            (new MahasiswaController())->handle();
            return;
        }

        // Endpoint buku
        if (
            $uri === 'buku' ||
            $uri === 'api/buku'
        ) {
            (new BukuController())->handle();
            return;
        }

        // Respons jika endpoint tidak ditemukan
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(404);

        echo json_encode(
            [
                'success' => false,
                'message' => 'Endpoint tidak ditemukan.',
            ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
    }
}
