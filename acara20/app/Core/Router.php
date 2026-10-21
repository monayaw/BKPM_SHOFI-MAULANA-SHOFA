<?php

class Router
{
    public function dispatch(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $base = '/WebServer/acara20/public';

        if (str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        $uri = trim($uri, '/');

        if ($uri === 'register') {
            (new AuthController())->register();
            return;
        }

        if ($uri === 'login') {
            (new AuthController())->login();
            return;
        }

        // GET mahasiswa tetap publik; method tulis dilindungi Bearer Token.
        if ($uri === '' || $uri === 'mahasiswa' || $uri === 'api/mahasiswa') {
            (new MahasiswaController())->handle();
            return;
        }

        Response::json(404, false, 'Endpoint tidak ditemukan.');
    }
}
