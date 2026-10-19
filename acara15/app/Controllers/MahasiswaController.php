<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Mahasiswa.php';
require_once __DIR__ . '/../Repositories/MahasiswaRepository.php';
require_once __DIR__ . '/../Services/MahasiswaService.php';

class MahasiswaController extends BaseController
{
    private MahasiswaRepository $repo;
    private MahasiswaService $service;

    public function __construct(MahasiswaRepository $repo)
    {
        $this->repo = $repo;
        $this->service = new MahasiswaService($repo);
    }

    // READ
    public function index(): void
    {
        $mahasiswa = $this->repo->all();

        $this->view('mahasiswa/index', [
            'mahasiswa' => $mahasiswa
        ]);
    }

    // FORM TAMBAH
    public function create(): void
    {
        $this->view('mahasiswa/create');
    }

    // CREATE
    public function store(): void
    {
        try {

            $mahasiswa = new Mahasiswa(
                trim($_POST['nim'] ?? ''),
                trim($_POST['nama'] ?? ''),
                trim($_POST['prodi'] ?? ''),
                trim($_POST['status'] ?? ''),
                !empty($_POST['email'])
                    ? trim($_POST['email'])
                    : null,
                !empty($_POST['dosen_id'])
                    ? (int) $_POST['dosen_id']
                    : null
            );

            $result = $this->service->create($mahasiswa);

            if ($result['success']) {

                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Data mahasiswa berhasil ditambahkan.'
                ];

            } else {

                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => $this->getErrorMessage(
                        $result['errors'],
                        'Data gagal disimpan.'
                    )
                ];
            }

        } catch (InvalidArgumentException $e) {

            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => $e->getMessage()
            ];

        } catch (Throwable $e) {

            // Simpan error ke log
            $this->logError($e);

            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Terjadi kesalahan. Data gagal disimpan.'
            ];
        }

        // PRG
        $this->redirect('/WebServer/acara15/public/mahasiswa');
    }

    // FORM EDIT
    public function edit(int $id): void
    {
        $mahasiswa = $this->repo->find($id);

        if (!$mahasiswa) {
            http_response_code(404);
            echo 'Data mahasiswa tidak ditemukan.';
            return;
        }

        $this->view('mahasiswa/edit', [
            'mahasiswa' => $mahasiswa
        ]);
    }

    // UPDATE
    public function update(int $id): void
    {
        try {

            $mahasiswa = new Mahasiswa(
                trim($_POST['nim'] ?? ''),
                trim($_POST['nama'] ?? ''),
                trim($_POST['prodi'] ?? ''),
                trim($_POST['status'] ?? ''),
                !empty($_POST['email'])
                    ? trim($_POST['email'])
                    : null,
                !empty($_POST['dosen_id'])
                    ? (int) $_POST['dosen_id']
                    : null
            );

            $result = $this->service->update(
                $id,
                $mahasiswa
            );

            if ($result['success']) {

                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Data mahasiswa berhasil diubah.'
                ];

            } else {

                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => $this->getErrorMessage(
                        $result['errors'],
                        'Data gagal diubah.'
                    )
                ];
            }

        } catch (InvalidArgumentException $e) {

            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => $e->getMessage()
            ];

        } catch (Throwable $e) {

            $this->logError($e);

            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Terjadi kesalahan. Data gagal diubah.'
            ];
        }

        // PRG
        $this->redirect('/WebServer/acara15/public/mahasiswa');
    }

    // DELETE
    public function destroy(int $id): void
    {
        try {

            $result = $this->service->delete($id);

            if ($result['success']) {

                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Data mahasiswa berhasil dihapus.'
                ];

            } else {

                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => $this->getErrorMessage(
                        $result['errors'],
                        'Data gagal dihapus.'
                    )
                ];
            }

        } catch (Throwable $e) {

            $this->logError($e);

            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Terjadi kesalahan. Data gagal dihapus.'
            ];
        }

        // PRG
        $this->redirect('/WebServer/acara15/public/mahasiswa');
    }

    // Mengambil pesan error
    private function getErrorMessage(
        array $errors,
        string $default
    ): string {

        foreach ([
            'nim',
            'nama',
            'prodi',
            'status',
            'email',
            'dosen_id',
            'general'
        ] as $field) {

            if (isset($errors[$field])) {
                return $errors[$field];
            }
        }

        return $default;
    }

    // Logging
    private function logError(Throwable $e): void
    {
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
    }
}