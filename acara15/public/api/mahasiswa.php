
<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../app/Core/Database.php';

function response(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

try {
    $db = Database::getInstance();
    $method = $_SERVER['REQUEST_METHOD'];

    // GET: mengambil data mahasiswa
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

            if (!$id || $id < 1) {
                response(400, [
                    'success' => false,
                    'message' => 'ID mahasiswa tidak valid.'
                ]);
            }

            $stmt = $db->prepare(
                "SELECT id, nim, nama, email
                 FROM mahasiswa WHERE id = :id"
            );
            $stmt->execute(['id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                response(404, [
                    'success' => false,
                    'message' => 'Mahasiswa tidak ditemukan.'
                ]);
            }

            response(200, [
                'success' => true,
                'message' => 'Data berhasil diambil.',
                'data' => $data
            ]);
        }

        $stmt = $db->query(
            "SELECT id, nim, nama, email
             FROM mahasiswa ORDER BY id DESC"
        );

        response(200, [
            'success' => true,
            'message' => 'Data berhasil diambil.',
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
        ]);
    }

    // POST: menambahkan mahasiswa
    if ($method === 'POST') {
        $input = json_decode(
            file_get_contents('php://input'),
            true
        );

        if (!is_array($input)) {
            response(400, [
                'success' => false,
                'message' => 'Format JSON tidak valid.'
            ]);
        }

        $nim = trim((string) ($input['nim'] ?? ''));
        $nama = trim((string) ($input['nama'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));

        if ($nim === '' || $nama === '' || $email === '') {
            response(422, [
                'success' => false,
                'message' => 'NIM, nama, dan email wajib diisi.'
            ]);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            response(422, [
                'success' => false,
                'message' => 'Format email tidak valid.'
            ]);
        }

        $check = $db->prepare(
            "SELECT COUNT(*) FROM mahasiswa WHERE nim = :nim"
        );
        $check->execute(['nim' => $nim]);

        if ((int) $check->fetchColumn() > 0) {
            response(409, [
                'success' => false,
                'message' => 'NIM sudah terdaftar.'
            ]);
        }

        // Untuk tabel acara sebelumnya yang memiliki
        // kolom prodi/status wajib diisi, berikan nilai default.
        $columns = $db->query("SHOW COLUMNS FROM mahasiswa")
                      ->fetchAll(PDO::FETCH_COLUMN);

        if (in_array('prodi', $columns, true)
            && in_array('status', $columns, true)) {

            $stmt = $db->prepare(
                "INSERT INTO mahasiswa
                 (nim, nama, email, prodi, status)
                 VALUES (:nim, :nama, :email, :prodi, :status)"
            );

            $stmt->execute([
                'nim' => $nim,
                'nama' => $nama,
                'email' => $email,
                'prodi' => $input['prodi'] ?? 'Belum ditentukan',
                'status' => 'aktif'
            ]);
        } else {
            $stmt = $db->prepare(
                "INSERT INTO mahasiswa (nim, nama, email)
                 VALUES (:nim, :nama, :email)"
            );

            $stmt->execute([
                'nim' => $nim,
                'nama' => $nama,
                'email' => $email
            ]);
        }

        response(201, [
            'success' => true,
            'message' => 'Mahasiswa berhasil ditambahkan.',
            'data' => [
                'id' => (int) $db->lastInsertId(),
                'nim' => $nim,
                'nama' => $nama,
                'email' => $email
            ]
        ]);
    }

    header('Allow: GET, POST');

    response(405, [
        'success' => false,
        'message' => 'Method tidak diizinkan.'
    ]);

} catch (Throwable $e) {
    // Detail error disimpan di log server,
    // tidak dikirimkan kepada pengguna API.
    error_log(
        date('Y-m-d H:i:s') . ' - ' .
        $e->getMessage() . PHP_EOL,
        3,
        __DIR__ . '/../../storage/logs/app.log'
    );

    response(500, [
        'success' => false,
        'message' => 'Terjadi kesalahan pada server.'
    ]);
}
