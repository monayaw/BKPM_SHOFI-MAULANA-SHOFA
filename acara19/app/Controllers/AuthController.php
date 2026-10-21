<?php

class AuthController
{
    private User $model;

    public function __construct()
    {
        $this->model = new User();
    }

    public function register(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Allow: POST');
            Response::json(405, false, 'Method tidak diizinkan. Gunakan POST.');
        }

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            Response::json(400, false, 'Format JSON tidak valid. Gunakan Body raw JSON.');
        }

        $username = trim((string) ($data['username'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');
        $errors = [];

        if ($username === '') {
            $errors['username'][] = 'Username wajib diisi.';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $errors['username'][] = 'Username harus terdiri dari 3 sampai 50 karakter.';
        }

        if ($email === '') {
            $errors['email'][] = 'Email wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'Format email tidak valid.';
        }

        if ($password === '') {
            $errors['password'][] = 'Password wajib diisi.';
        } elseif (strlen($password) < 8) {
            $errors['password'][] = 'Password minimal 8 karakter.';
        }

        if ($errors !== []) {
            Response::json(422, false, 'Validasi registrasi gagal.', null, $errors);
        }

        try {
            if ($this->model->existsByUsernameOrEmail($username, $email)) {
                Response::json(409, false, 'Username atau email sudah terdaftar.');
            }

            $id = $this->model->create([
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]);

            Response::json(201, true, 'Registrasi berhasil.', [
                'id' => $id,
                'username' => $username,
                'email' => $email,
            ]);
        } catch (PDOException $e) {
            error_log('[Acara19 register] ' . $e->getMessage());
            Response::json(500, false, 'Registrasi gagal karena kesalahan database.');
        } catch (Throwable $e) {
            error_log('[Acara19 register] ' . $e->getMessage());
            Response::json(500, false, 'Terjadi kesalahan server.');
        }
    }
}
