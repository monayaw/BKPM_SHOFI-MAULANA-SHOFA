
<?php

class MahasiswaController
{
    private Mahasiswa $model;

    public function __construct()
    {
        $this->model = new Mahasiswa();
    }

    private function respond(int $status, array $payload): void
    {
        http_response_code($status);

        echo json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );

        exit();
    }

    private function input(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            $this->respond(400, [
                'success' => false,
                'message' => 'Format JSON tidak valid. Gunakan Body raw JSON.',
            ]);
        }

        return $data;
    }

    private function validate(array $input, ?int $id = null): array
    {
        $nim = trim((string) ($input['nim'] ?? ''));
        $nama = trim((string) ($input['nama'] ?? ''));
        $prodi = trim((string) ($input['prodi'] ?? ''));
        $status = strtolower(trim((string) ($input['status'] ?? '')));
        $email = trim((string) ($input['email'] ?? ''));
        $dosen = $input['dosen_id'] ?? null;

        // Validasi field wajib
        if (
            $nim === '' ||
            $nama === '' ||
            $prodi === '' ||
            $status === '' ||
            $email === ''
        ) {
            $this->respond(422, [
                'success' => false,
                'message' => 'NIM, nama, prodi, status, dan email wajib diisi.',
            ]);
        }

        // Validasi status mahasiswa
        if (!in_array($status, ['aktif', 'cuti', 'lulus'], true)) {
            $this->respond(422, [
                'success' => false,
                'message' => 'Status harus aktif, cuti, atau lulus.',
            ]);
        }

        // Validasi format email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->respond(422, [
                'success' => false,
                'message' => 'Format email tidak valid.',
            ]);
        }

        // Validasi NIM agar tidak duplikat
        if ($this->model->nimExists($nim, $id)) {
            $this->respond(409, [
                'success' => false,
                'message' => 'NIM sudah terdaftar.',
            ]);
        }

        return [
            'nim' => $nim,
            'nama' => $nama,
            'prodi' => $prodi,
            'status' => $status,
            'email' => $email,
            'dosen_id' => $dosen === '' ? null : $dosen,
        ];
    }

    public function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $id = null;

            // Validasi parameter ID
            if (isset($_GET['id'])) {
                $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

                if (!$id || $id < 1) {
                    $this->respond(400, [
                        'success' => false,
                        'message' => 'ID mahasiswa tidak valid.',
                    ]);
                }
            }

            // GET: Mengambil data mahasiswa
            if ($method === 'GET') {
                if ($id !== null) {
                    $data = $this->model->find($id);

                    if (!$data) {
                        $this->respond(404, [
                            'success' => false,
                            'message' => 'Mahasiswa tidak ditemukan.',
                        ]);
                    }

                    $this->respond(200, [
                        'success' => true,
                        'message' => 'Data mahasiswa berhasil diambil.',
                        'data' => $data,
                    ]);
                }

                $this->respond(200, [
                    'success' => true,
                    'message' => 'Data mahasiswa berhasil diambil.',
                    'data' => $this->model->getAll(),
                ]);
            }

            // POST: Menambahkan data mahasiswa
            if ($method === 'POST') {
                $data = $this->validate($this->input());
                $newId = $this->model->create($data);

                $this->respond(201, [
                    'success' => true,
                    'message' => 'Mahasiswa berhasil ditambahkan.',
                    'data' => $this->model->find($newId),
                ]);
            }

            // PUT dan PATCH: Memperbarui data mahasiswa
            if ($method === 'PUT' || $method === 'PATCH') {
                if ($id === null) {
                    $this->respond(400, [
                        'success' => false,
                        'message' => 'Parameter id wajib diisi, contoh ?id=1.',
                    ]);
                }

                if (!$this->model->find($id)) {
                    $this->respond(404, [
                        'success' => false,
                        'message' => 'Mahasiswa tidak ditemukan.',
                    ]);
                }

                $input = $this->input();

                // PATCH hanya memperbarui field yang dikirim
                if ($method === 'PATCH') {
                    $current = $this->model->find($id);
                    $input = array_merge($current, $input);
                }

                $data = $this->validate($input, $id);

                $this->model->update($id, $data);

                $this->respond(200, [
                    'success' => true,
                    'message' => 'Data mahasiswa berhasil diperbarui.',
                    'data' => $this->model->find($id),
                ]);
            }

            // DELETE: Menghapus data mahasiswa
            if ($method === 'DELETE') {
                if ($id === null) {
                    $this->respond(400, [
                        'success' => false,
                        'message' => 'Parameter id wajib diisi, contoh ?id=1.',
                    ]);
                }

                if (!$this->model->delete($id)) {
                    $this->respond(404, [
                        'success' => false,
                        'message' => 'Mahasiswa tidak ditemukan atau sudah dihapus.',
                    ]);
                }

                $this->respond(200, [
                    'success' => true,
                    'message' => 'Data mahasiswa berhasil dihapus.',
                ]);
            }

            // Menangani HTTP method yang tidak didukung
            header('Allow: GET, POST, PUT, PATCH, DELETE');

            $this->respond(405, [
                'success' => false,
                'message' => 'Method tidak diizinkan.',
            ]);
        } catch (Throwable $e) {
            error_log('[Acara17 API] ' . $e->getMessage());

            $this->respond(500, [
                'success' => false,
                'message' => 'Terjadi kesalahan server. Periksa konfigurasi database dan storage/logs/php-error.log.',
            ]);
        }
    }
}
