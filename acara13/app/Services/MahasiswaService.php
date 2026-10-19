
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

    public function create(Mahasiswa $mahasiswa): array
    {
        $errors = $this->validate($mahasiswa);

        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }

        try {
            $success = $this->repo->create($mahasiswa);

            return [
                'success' => $success,
                'errors' => $success ? [] : [
                    'general' => 'Data gagal disimpan.'
                ]
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'errors' => [
                    'general' => 'Data gagal disimpan.'
                ]
            ];
        }
    }

    public function update(
        int $id,
        Mahasiswa $mahasiswa
    ): array {
        $errors = $this->validate($mahasiswa, $id);

        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }

        try {
            $success = $this->repo->update($id, $mahasiswa);

            return [
                'success' => $success,
                'errors' => $success ? [] : [
                    'general' => 'Data gagal diubah.'
                ]
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'errors' => [
                    'general' => 'Data gagal diubah.'
                ]
            ];
        }
    }

    private function validate(
        Mahasiswa $mahasiswa,
        ?int $excludeId = null
    ): array {
        $errors = [];

        $nim = trim($mahasiswa->getNim());
        $nama = trim($mahasiswa->getNama());
        $prodi = trim($mahasiswa->getProdi());
        $status = trim($mahasiswa->getStatus());

        if ($nim === '') {
            $errors['nim'] = 'NIM wajib diisi.';
        } elseif (
            $this->repo->existsByNim($nim, $excludeId)
        ) {
            $errors['nim'] = 'NIM sudah terdaftar.';
        }

        if ($nama === '') {
            $errors['nama'] = 'Nama wajib diisi.';
        }

        if ($prodi === '') {
            $errors['prodi'] = 'Program studi wajib diisi.';
        }

        if ($status === '') {
            $errors['status'] = 'Status wajib diisi.';
        }

        return $errors;
    }
}
