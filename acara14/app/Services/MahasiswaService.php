<?php

require_once __DIR__ . '/../Models/Mahasiswa.php';
require_once __DIR__ . '/../Repositories/MahasiswaRepository.php';

class MahasiswaService
{
    private MahasiswaRepository $repo;

    public function __construct(MahasiswaRepository $repo)
    {
        $this->repo = $repo;
    }

    // =========================
    // CREATE
    // =========================
    public function create(Mahasiswa $mahasiswa): array
    {
        try {
            $errors = $this->validate($mahasiswa);

            if (!empty($errors)) {
                return [
                    'success' => false,
                    'errors' => $errors
                ];
            }

            $success = $this->repo->create($mahasiswa);

            if (!$success) {
                throw new Exception('Data mahasiswa gagal disimpan ke database.');
            }

            return [
                'success' => true,
                'errors' => []
            ];

        } catch (Throwable $e) {
            $this->logError($e);

            return [
                'success' => false,
                'errors' => [
                    'general' => 'Terjadi kesalahan. Data gagal disimpan.'
                ]
            ];
        }
    }

    // =========================
    // UPDATE
    // =========================
    public function update(int $id, Mahasiswa $mahasiswa): array
    {
        try {
            if ($this->repo->find($id) === null) {
                return [
                    'success' => false,
                    'errors' => [
                        'general' => 'Data mahasiswa tidak ditemukan.'
                    ]
                ];
            }

            $errors = $this->validate($mahasiswa, $id);

            if (!empty($errors)) {
                return [
                    'success' => false,
                    'errors' => $errors
                ];
            }

            $success = $this->repo->update($id, $mahasiswa);

            if (!$success) {
                throw new Exception('Data mahasiswa gagal diubah di database.');
            }

            return [
                'success' => true,
                'errors' => []
            ];

        } catch (Throwable $e) {
            $this->logError($e);

            return [
                'success' => false,
                'errors' => [
                    'general' => 'Terjadi kesalahan. Data gagal diubah.'
                ]
            ];
        }
    }

    // =========================
    // DELETE
    // =========================
    public function delete(int $id): array
    {
        try {
            if ($this->repo->find($id) === null) {
                return [
                    'success' => false,
                    'errors' => [
                        'general' => 'Data mahasiswa tidak ditemukan.'
                    ]
                ];
            }

            $success = $this->repo->delete($id);

            if (!$success) {
                throw new Exception('Data mahasiswa gagal dihapus dari database.');
            }

            return [
                'success' => true,
                'errors' => []
            ];

        } catch (Throwable $e) {
            $this->logError($e);

            return [
                'success' => false,
                'errors' => [
                    'general' => 'Terjadi kesalahan. Data gagal dihapus.'
                ]
            ];
        }
    }

    // =========================
    // VALIDASI
    // =========================
    private function validate(
        Mahasiswa $mahasiswa,
        ?int $excludeId = null
    ): array {
        $errors = [];

        $nim = trim($mahasiswa->getNim());
        $nama = trim($mahasiswa->getNama());
        $prodi = trim($mahasiswa->getProdi());
        $status = strtolower(trim($mahasiswa->getStatus()));
        $email = trim($mahasiswa->getEmail() ?? '');

        // NIM
        if ($nim === '') {
            $errors['nim'] = 'NIM wajib diisi.';
        } elseif (!ctype_digit($nim)) {
            $errors['nim'] = 'NIM harus berupa angka.';
        } elseif (strlen($nim) > 20) {
            $errors['nim'] = 'NIM maksimal 20 karakter.';
        } else {
            try {
                if ($this->repo->existsByNim($nim, $excludeId)) {
                    $errors['nim'] = 'NIM sudah terdaftar.';
                }
            } catch (Throwable $e) {
                $this->logError($e);

                $errors['general'] =
                    'Terjadi kesalahan saat memeriksa NIM.';
            }
        }

        // NAMA
        if ($nama === '') {
            $errors['nama'] = 'Nama wajib diisi.';
        } elseif (strlen($nama) > 100) {
            $errors['nama'] = 'Nama maksimal 100 karakter.';
        }

        // PRODI
        if ($prodi === '') {
            $errors['prodi'] = 'Program studi wajib diisi.';
        } elseif (strlen($prodi) > 100) {
            $errors['prodi'] =
                'Program studi maksimal 100 karakter.';
        }

        // STATUS
        $allowedStatus = ['aktif', 'cuti', 'lulus'];

        if ($status === '') {
            $errors['status'] = 'Status wajib diisi.';
        } elseif (!in_array($status, $allowedStatus, true)) {
            $errors['status'] =
                'Status mahasiswa tidak valid.';
        }

        // EMAIL
        if ($email === '') {
            $errors['email'] = 'Email wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email tidak valid.';
        }

        return $errors;
    }

    // =========================
    // LOGGING
    // =========================
    private function logError(Throwable $e): void
    {
        $logDir = __DIR__ . '/../../storage/logs';
        $logFile = $logDir . '/app.log';

        // Buat folder jika belum ada
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $message = sprintf(
            "[%s] %s: %s | File: %s | Line: %d%s",
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
            FILE_APPEND | LOCK_EX
        );
    }
}