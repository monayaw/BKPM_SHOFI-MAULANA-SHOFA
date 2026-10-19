<?php

session_start();

require_once __DIR__ . '/../routes/web.php';

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Models/BaseModel.php';
require_once __DIR__ . '/../app/Models/Mahasiswa.php';
require_once __DIR__ . '/../app/Repositories/MahasiswaRepository.php';

require_once __DIR__ . '/../app/Controllers/BaseController.php';
require_once __DIR__ . '/../app/Controllers/MahasiswaController.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/HomeController.php';

require_once __DIR__ . '/../app/Middleware/AuthMiddleware.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$base = '/WebServer/acara14/public';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (str_starts_with($uri, $base)) {
    $uri = substr($uri, strlen($base));
}

if ($uri === '') {
    $uri = '/';
}


/*
|--------------------------------------------------------------------------
| Fungsi koneksi database
|--------------------------------------------------------------------------
| Jika database error, user hanya melihat pesan aman.
| Detail error sudah dicatat oleh Database.php ke app.log.
*/

function getDatabase(): PDO
{
    try {

        return Database::getInstance();

    } catch (Throwable $e) {

        http_response_code(500);

        echo '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Terjadi Kesalahan</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 50px;
                }

                .error {
                    padding: 20px;
                    border: 1px solid #dc3545;
                    background: #f8d7da;
                    color: #842029;
                    border-radius: 5px;
                }
            </style>
        </head>

        <body>

            <div class="error">
                <h2>Terjadi Kesalahan</h2>

                <p>
                    Terjadi kesalahan pada sistem.
                    Silakan coba lagi.
                </p>
            </div>

        </body>
        </html>
        ';

        exit;
    }
}


/* Route parameter mahasiswa */

if (
    $method === 'GET' &&
    preg_match('#^/mahasiswa/([0-9]+)/edit$#', $uri, $matches)
) {
    (new AuthMiddleware())->handle();

    $controller = new MahasiswaController(
        new MahasiswaRepository(getDatabase())
    );

    $controller->edit((int) $matches[1]);

    exit;
}


if (
    $method === 'POST' &&
    preg_match('#^/mahasiswa/([0-9]+)$#', $uri, $matches)
) {
    (new AuthMiddleware())->handle();

    $controller = new MahasiswaController(
        new MahasiswaRepository(getDatabase())
    );

    $controller->update((int) $matches[1]);

    exit;
}


if (
    $method === 'POST' &&
    preg_match('#^/mahasiswa/([0-9]+)/delete$#', $uri, $matches)
) {
    (new AuthMiddleware())->handle();

    $controller = new MahasiswaController(
        new MahasiswaRepository(getDatabase())
    );

    $controller->destroy((int) $matches[1]);

    exit;
}


/* Cari route */

$route = $routes[$method][$uri] ?? null;

if ($route === null) {

    http_response_code(404);

    echo '<h1>404 - Halaman Tidak Ditemukan</h1>';

    exit;
}


/* Middleware */

if (isset($route['middleware'])) {

    foreach ($route['middleware'] as $middlewareName) {

        if ($middlewareName === 'AuthMiddleware') {

            (new AuthMiddleware())->handle();
        }
    }
}


/* Controller */

$controllerName = $route['controller'];
$action = $route['action'];


if ($controllerName === 'MahasiswaController') {

    $controller = new MahasiswaController(
        new MahasiswaRepository(getDatabase())
    );

} elseif ($controllerName === 'AuthController') {

    $controller = new AuthController();

} elseif ($controllerName === 'HomeController') {

    $controller = new HomeController();

} else {

    http_response_code(404);

    echo '<h1>Controller Tidak Ditemukan</h1>';

    exit;
}


/* Jalankan action */

if (!method_exists($controller, $action)) {

    http_response_code(500);

    echo "Method {$action} tidak ditemukan pada {$controllerName}.";

    exit;
}


$controller->$action();