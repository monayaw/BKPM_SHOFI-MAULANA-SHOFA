<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../app/Core/Database.php';

function response(int $status, array $data): void
{
    http_response_code($status);

    echo json_encode(
        $data,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );

    exit;
}

try {

    $db = Database::getInstance();

    $method = $_SERVER['REQUEST_METHOD'];


    /*
    |--------------------------------------------------------------------------
    | GET
    |--------------------------------------------------------------------------
    */

    if ($method === 'GET') {

        // GET /api/mahasiswa.php?id=1
        if (isset($_GET['id'])) {

            $id = filter_var(
                $_GET['id'],
                FILTER_VALIDATE_INT
            );

            if (!$id || $id < 1) {

                response(400, [
                    'success' => false,
                    'message' => 'ID mahasiswa tidak valid.'
                ]);
            }

            $stmt = $db->prepare("
                SELECT
                    id,
                    nim,
                    nama,
                    prodi,
                    status,
                    email,
                    dosen_id
                FROM mahasiswa
                WHERE id = :id
            ");

            $stmt->execute([
                'id' => $id
            ]);

            $data = $stmt->fetch();

            if (!$data) {

                response(404, [
                    'success' => false,
                    'message' => 'Mahasiswa tidak ditemukan.'
                ]);
            }

            response(200, [
                'success' => true,
                'message' => 'Data mahasiswa berhasil diambil.',
                'data' => $data
            ]);
        }


        // GET /api/mahasiswa.php
        $stmt = $db->query("
            SELECT
                id,
                nim,
                nama,
                prodi,
                status,
                email,
                dosen_id
            FROM mahasiswa
            ORDER BY id DESC
        ");

        $data = $stmt->fetchAll();

        response(200, [
            'success' => true,
            'message' => 'Data mahasiswa berhasil diambil.',
            'data' => $data
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | POST
    |--------------------------------------------------------------------------
    */

    if ($method === 'POST') {

        $json = file_get_contents('php://input');

        $input = json_decode($json, true);

        if (!is_array($input)) {

            response(400, [
                'success' => false,
                'message' => 'Format JSON tidak valid.'
            ]);
        }


        $nim = trim((string) ($input['nim'] ?? ''));
        $nama = trim((string) ($input['nama'] ?? ''));
        $prodi = trim((string) ($input['prodi'] ?? ''));
        $status = strtolower(
            trim((string) ($input['status'] ?? ''))
        );
        $email = trim((string) ($input['email'] ?? ''));
        $dosen_id = $input['dosen_id'] ?? null;


        // Validasi data wajib
        if (
            $nim === '' ||
            $nama === '' ||
            $prodi === '' ||
            $status === '' ||
            $email === ''
        ) {

            response(422, [
                'success' => false,
                'message' => 'NIM, nama, prodi, status, dan email wajib diisi.'
            ]);
        }


        // Validasi status
        $allowedStatus = [
            'aktif',
            'cuti',
            'lulus'
        ];

        if (!in_array($status, $allowedStatus, true)) {

            response(422, [
                'success' => false,
                'message' => 'Status harus aktif, cuti, atau lulus.'
            ]);
        }


        // Validasi email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            response(422, [
                'success' => false,
                'message' => 'Format email tidak valid.'
            ]);
        }


        // Cek NIM
        $check = $db->prepare("
            SELECT COUNT(*)
            FROM mahasiswa
            WHERE nim = :nim
        ");

        $check->execute([
            'nim' => $nim
        ]);

        if ((int) $check->fetchColumn() > 0) {

            response(409, [
                'success' => false,
                'message' => 'NIM sudah terdaftar.'
            ]);
        }


        // Insert
        $stmt = $db->prepare("
            INSERT INTO mahasiswa
            (
                nim,
                nama,
                prodi,
                status,
                email,
                dosen_id
            )
            VALUES
            (
                :nim,
                :nama,
                :prodi,
                :status,
                :email,
                :dosen_id
            )
        ");

        $stmt->execute([
            'nim' => $nim,
            'nama' => $nama,
            'prodi' => $prodi,
            'status' => $status,
            'email' => $email,
            'dosen_id' => $dosen_id
        ]);


        $id = (int) $db->lastInsertId();


        response(201, [
            'success' => true,
            'message' => 'Mahasiswa berhasil ditambahkan.',
            'data' => [
                'id' => $id,
                'nim' => $nim,
                'nama' => $nama,
                'prodi' => $prodi,
                'status' => $status,
                'email' => $email,
                'dosen_id' => $dosen_id
            ]
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | PUT
    |--------------------------------------------------------------------------
    */

    if ($method === 'PUT') {

        $id = filter_var(
            $_GET['id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$id || $id < 1) {

            response(400, [
                'success' => false,
                'message' => 'ID mahasiswa tidak valid.'
            ]);
        }


        $json = file_get_contents('php://input');

        $input = json_decode($json, true);

        if (!is_array($input)) {

            response(400, [
                'success' => false,
                'message' => 'Format JSON tidak valid.'
            ]);
        }


        $nim = trim((string) ($input['nim'] ?? ''));
        $nama = trim((string) ($input['nama'] ?? ''));
        $prodi = trim((string) ($input['prodi'] ?? ''));
        $status = strtolower(
            trim((string) ($input['status'] ?? ''))
        );
        $email = trim((string) ($input['email'] ?? ''));
        $dosen_id = $input['dosen_id'] ?? null;


        if (
            $nim === '' ||
            $nama === '' ||
            $prodi === '' ||
            $status === '' ||
            $email === ''
        ) {

            response(422, [
                'success' => false,
                'message' => 'NIM, nama, prodi, status, dan email wajib diisi.'
            ]);
        }


        $allowedStatus = [
            'aktif',
            'cuti',
            'lulus'
        ];

        if (!in_array($status, $allowedStatus, true)) {

            response(422, [
                'success' => false,
                'message' => 'Status harus aktif, cuti, atau lulus.'
            ]);
        }


        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            response(422, [
                'success' => false,
                'message' => 'Format email tidak valid.'
            ]);
        }


        // Cek mahasiswa
        $check = $db->prepare("
            SELECT id
            FROM mahasiswa
            WHERE id = :id
        ");

        $check->execute([
            'id' => $id
        ]);

        if (!$check->fetch()) {

            response(404, [
                'success' => false,
                'message' => 'Mahasiswa tidak ditemukan.'
            ]);
        }


        // Cek NIM agar tidak dipakai mahasiswa lain
        $checkNim = $db->prepare("
            SELECT id
            FROM mahasiswa
            WHERE nim = :nim
            AND id != :id
        ");

        $checkNim->execute([
            'nim' => $nim,
            'id' => $id
        ]);

        if ($checkNim->fetch()) {

            response(409, [
                'success' => false,
                'message' => 'NIM sudah digunakan mahasiswa lain.'
            ]);
        }


        // Update
        $stmt = $db->prepare("
            UPDATE mahasiswa SET
                nim = :nim,
                nama = :nama,
                prodi = :prodi,
                status = :status,
                email = :email,
                dosen_id = :dosen_id
            WHERE id = :id
        ");

        $stmt->execute([
            'nim' => $nim,
            'nama' => $nama,
            'prodi' => $prodi,
            'status' => $status,
            'email' => $email,
            'dosen_id' => $dosen_id,
            'id' => $id
        ]);


        response(200, [
            'success' => true,
            'message' => 'Data mahasiswa berhasil diubah.',
            'data' => [
                'id' => $id,
                'nim' => $nim,
                'nama' => $nama,
                'prodi' => $prodi,
                'status' => $status,
                'email' => $email,
                'dosen_id' => $dosen_id
            ]
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    if ($method === 'DELETE') {

        $id = filter_var(
            $_GET['id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$id || $id < 1) {

            response(400, [
                'success' => false,
                'message' => 'ID mahasiswa tidak valid.'
            ]);
        }


        // Cek mahasiswa
        $check = $db->prepare("
            SELECT id
            FROM mahasiswa
            WHERE id = :id
        ");

        $check->execute([
            'id' => $id
        ]);

        if (!$check->fetch()) {

            response(404, [
                'success' => false,
                'message' => 'Mahasiswa tidak ditemukan.'
            ]);
        }


        // Delete
        $stmt = $db->prepare("
            DELETE FROM mahasiswa
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $id
        ]);


        response(200, [
            'success' => true,
            'message' => 'Data mahasiswa berhasil dihapus.'
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | METHOD TIDAK DIIZINKAN
    |--------------------------------------------------------------------------
    */

    header('Allow: GET, POST, PUT, DELETE');

    response(405, [
        'success' => false,
        'message' => 'Method tidak diizinkan.'
    ]);


} catch (Throwable $e) {

    /*
    |--------------------------------------------------------------------------
    | LOG ERROR
    |--------------------------------------------------------------------------
    */

    $logDir = __DIR__ . '/../../storage/logs';
    $logFile = $logDir . '/app.log';

    if (!is_dir($logDir)) {
        mkdir($logDir, 0750, true);
    }

    $message = sprintf(
        "[%s] %s: %s in %s:%d%s",
        date('Y-m-d H:i:s'),
        get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        PHP_EOL
    );

    file_put_contents(
        $logFile,
        $message,
        FILE_APPEND
    );


    // Pesan aman untuk pengguna API
    response(500, [
        'success' => false,
        'message' => 'Terjadi kesalahan pada server.'
    ]);
}