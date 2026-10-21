
<?php

require_once __DIR__ . '/../Models/Buku.php';

class BukuController
{
    private Buku $model;

    public function __construct()
    {
        $this->model = new Buku();
    }

    private function respond(int $status, array $data): void
    {
        http_response_code($status);

        echo json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );

        exit();
    }

    public function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

            if ($method === 'GET') {
                $data = $id
                    ? $this->model->find($id)
                    : $this->model->getAll();

                if ($id && !$data) {
                    $this->respond(404, [
                        'success' => false,
                        'message' => 'Buku tidak ditemukan.',
                    ]);
                }

                $this->respond(200, [
                    'success' => true,
                    'data' => $data,
                ]);
            }

            if ($method === 'POST') {
                $input = json_decode(
                    file_get_contents('php://input'),
                    true
                );

                if (
                    !is_array($input) ||
                    empty($input['judul']) ||
                    empty($input['penulis']) ||
                    empty($input['tahun'])
                ) {
                    $this->respond(422, [
                        'success' => false,
                        'message' => 'Judul, penulis, dan tahun wajib diisi.',
                    ]);
                }

                $newId = $this->model->create($input);

                $this->respond(201, [
                    'success' => true,
                    'message' => 'Buku berhasil ditambahkan.',
                    'data' => $this->model->find($newId),
                ]);
            }

            if ($method === 'PUT') {
                if (!$id || !$this->model->find($id)) {
                    $this->respond(404, [
                        'success' => false,
                        'message' => 'Buku tidak ditemukan.',
                    ]);
                }

                $input = json_decode(
                    file_get_contents('php://input'),
                    true
                );

                if (
                    !is_array($input) ||
                    empty($input['judul']) ||
                    empty($input['penulis']) ||
                    empty($input['tahun'])
                ) {
                    $this->respond(422, [
                        'success' => false,
                        'message' => 'Judul, penulis, dan tahun wajib diisi.',
                    ]);
                }

                $this->model->update($id, $input);

                $this->respond(200, [
                    'success' => true,
                    'message' => 'Buku berhasil diperbarui.',
                    'data' => $this->model->find($id),
                ]);
            }

            if ($method === 'DELETE') {
                if (!$id || !$this->model->delete($id)) {
                    $this->respond(404, [
                        'success' => false,
                        'message' => 'Buku tidak ditemukan.',
                    ]);
                }

                $this->respond(200, [
                    'success' => true,
                    'message' => 'Buku berhasil dihapus.',
                ]);
            }

            header('Allow: GET, POST, PUT, DELETE');

            $this->respond(405, [
                'success' => false,
                'message' => 'Method tidak diizinkan.',
            ]);
        } catch (Throwable $e) {
            error_log('[Acara17 Buku API] ' . $e->getMessage());

            $this->respond(500, [
                'success' => false,
                'message' => 'Terjadi kesalahan server.',
            ]);
        }
    }
}
