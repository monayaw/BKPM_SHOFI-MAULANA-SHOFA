<?php

class MahasiswaController
{
    private Mahasiswa $model;

    public function __construct()
    {
        $this->model = new Mahasiswa();
    }

    private function input(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            Response::json(400, false, 'Format JSON tidak valid. Gunakan Body raw JSON.');
        }

        return $data;
    }

    private function validate(array $input, ?int $exceptId = null): array
    {
        $errors = [];
        $nim = trim((string) ($input['nim'] ?? ''));
        $nama = trim((string) ($input['nama'] ?? ''));
        $prodi = trim((string) ($input['prodi'] ?? ''));
        $status = strtolower(trim((string) ($input['status'] ?? '')));
        $email = trim((string) ($input['email'] ?? ''));
        $dosen = $input['dosen_id'] ?? null;

        foreach (['nim' => $nim, 'nama' => $nama, 'prodi' => $prodi, 'status' => $status, 'email' => $email] as $field => $value) {
            if ($value === '') {
                $errors[$field][] = ucfirst($field) . ' wajib diisi.';
            }
        }

        if ($nim !== '' && !preg_match('/^[0-9]{9}$/', $nim)) {
            $errors['nim'][] = 'NIM harus terdiri dari 9 digit angka.';
        }
        if ($status !== '' && !in_array($status, ['aktif', 'cuti', 'lulus'], true)) {
            $errors['status'][] = 'Status harus aktif, cuti, atau lulus.';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'Format email tidak valid.';
        }

        if ($errors !== []) {
            Response::json(422, false, 'Validasi data gagal.', null, $errors);
        }

        if ($this->model->nimExists($nim, $exceptId)) {
            Response::json(409, false, 'NIM sudah terdaftar.', null, ['nim' => ['NIM harus unik.']]);
        }

        return [
            'nim' => $nim,
            'nama' => $nama,
            'prodi' => $prodi,
            'status' => $status,
            'email' => $email,
            'dosen_id' => ($dosen === '' || $dosen === null) ? null : (int) $dosen,
        ];
    }

    public function handle(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $id = null;

        if (isset($_GET['id'])) {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            if ($id === false || $id === null || $id < 1) {
                Response::json(400, false, 'ID mahasiswa tidak valid.');
            }
        }

        try {
            switch ($method) {
                case 'GET':
                    if ($id !== null) {
                        $student = $this->model->find($id);
                        if ($student === null) Response::json(404, false, 'Mahasiswa tidak ditemukan.');
                        Response::json(200, true, 'Data mahasiswa berhasil diambil.', $student);
                    }
                    Response::json(200, true, 'Data mahasiswa berhasil diambil.', $this->model->getAll());

                case 'POST':
                    $data = $this->validate($this->input());
                    $newId = $this->model->create($data);
                    Response::json(201, true, 'Mahasiswa berhasil ditambahkan.', $this->model->find($newId));

                case 'PUT':
                case 'PATCH':
                    if ($id === null) Response::json(400, false, 'Parameter id wajib diisi, contoh ?id=1.');
                    $current = $this->model->find($id);
                    if ($current === null) Response::json(404, false, 'Mahasiswa tidak ditemukan.');
                    $input = $this->input();
                    if ($method === 'PATCH') $input = array_merge($current, $input);
                    $data = $this->validate($input, $id);
                    $this->model->update($id, $data);
                    Response::json(200, true, 'Data mahasiswa berhasil diperbarui.', $this->model->find($id));

                case 'DELETE':
                    if ($id === null) Response::json(400, false, 'Parameter id wajib diisi, contoh ?id=1.');
                    if (!$this->model->delete($id)) Response::json(404, false, 'Mahasiswa tidak ditemukan atau sudah dihapus.');
                    Response::json(200, true, 'Data mahasiswa berhasil dihapus.');

                default:
                    header('Allow: GET, POST, PUT, PATCH, DELETE');
                    Response::json(405, false, 'Method tidak diizinkan.');
            }
        } catch (PDOException $e) {
            error_log('[Acara18 API] ' . $e->getMessage());
            Response::json(500, false, 'Terjadi kesalahan database. Periksa struktur tabel mahasiswa dan konfigurasi database.');
        } catch (Throwable $e) {
            error_log('[Acara18 API] ' . $e->getMessage());
            Response::json(500, false, 'Terjadi kesalahan server. Periksa log PHP.');
        }
    }
}
