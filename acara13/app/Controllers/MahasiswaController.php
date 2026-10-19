
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

    public function index(): void
    {
        $mahasiswa = $this->repo->all();

        $this->view('mahasiswa/index', [
            'mahasiswa' => $mahasiswa
        ]);
    }

    public function create(): void
    {
        $this->view('mahasiswa/create');
    }

    public function store(): void
    {
        try {
            $mahasiswa = new Mahasiswa(
                trim($_POST['nim'] ?? ''),
                trim($_POST['nama'] ?? ''),
                trim($_POST['prodi'] ?? ''),
                trim($_POST['status'] ?? ''),
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
        }

        $this->redirect('/WebServer/acara13/public/mahasiswa');
    }

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

    public function update(int $id): void
    {
        try {
            $mahasiswa = new Mahasiswa(
                trim($_POST['nim'] ?? ''),
                trim($_POST['nama'] ?? ''),
                trim($_POST['prodi'] ?? ''),
                trim($_POST['status'] ?? ''),
                !empty($_POST['dosen_id'])
                    ? (int) $_POST['dosen_id']
                    : null
            );

            $result = $this->service->update($id, $mahasiswa);

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
        }

        $this->redirect('/WebServer/acara13/public/mahasiswa');
    }

    public function destroy(int $id): void
    {
        if ($this->repo->delete($id)) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Data mahasiswa berhasil dihapus.'
            ];
        } else {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Data gagal dihapus.'
            ];
        }

        $this->redirect('/WebServer/acara13/public/mahasiswa');
    }

    private function getErrorMessage(
        array $errors,
        string $default
    ): string {
        foreach ([
            'nim',
            'nama',
            'prodi',
            'status',
            'general'
        ] as $field) {
            if (isset($errors[$field])) {
                return $errors[$field];
            }
        }

        return $default;
    }
}
