<?php

class MahasiswaController
{
    private Mahasiswa $model;

    private const MAX_FILE_SIZE = 2 * 1024 * 1024;
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    public function __construct()
    {
        $this->model = new Mahasiswa();
    }

    private function respond(int $status, bool $success, string $message, mixed $data = null, array $errors = []): void
    {
        Response::json($status, $success, $message, $data, $errors);
    }

    private function parseMultipartPut(): void
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (!preg_match('/boundary=(?:"([^"]+)"|([^;]+))/i', $contentType, $matches)) {
            return;
        }

        $boundary = $matches[1] !== '' ? $matches[1] : trim($matches[2]);
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return;
        }

        $parts = preg_split('/--' . preg_quote($boundary, '/') . '(?:--)?\r\n/', $raw);
        foreach ($parts ?: [] as $part) {
            $part = preg_replace('/\r\n--\s*$/', '', $part);
            $part = ltrim($part, "\r\n");
            if ($part === '' || $part === "--\r\n" || strpos($part, "\r\n\r\n") === false) {
                continue;
            }

            [$headersRaw, $value] = explode("\r\n\r\n", $part, 2);
            $value = preg_replace('/\r\n$/', '', $value);
            if (!preg_match('/Content-Disposition:\s*form-data;\s*name="([^"]+)"(?:;\s*filename="([^"]*)")?/i', $headersRaw, $disp)) {
                continue;
            }

            $field = $disp[1];
            if (array_key_exists(2, $disp)) {
                $filename = basename(str_replace('\\', '/', $disp[2]));
                if ($filename === '') {
                    continue;
                }
                $temp = tempnam(sys_get_temp_dir(), 'acara27_');
                file_put_contents($temp, $value);
                register_shutdown_function(static function () use ($temp): void {
                    if (is_file($temp)) {
                        @unlink($temp);
                    }
                });
                $_FILES[$field] = [
                    'name' => $filename,
                    'type' => '',
                    'tmp_name' => $temp,
                    'error' => UPLOAD_ERR_OK,
                    'size' => strlen($value),
                    'manual_tmp' => true,
                ];
            } else {
                $_POST[$field] = $value;
            }
        }
    }

    private function readInput(): array
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'PUT' &&
            str_contains(strtolower($_SERVER['CONTENT_TYPE'] ?? ''), 'multipart/form-data')) {
            $this->parseMultipartPut();
        }
        // FormData dipakai untuk upload; JSON tetap didukung untuk request lama.
        if (!empty($_POST)) {
            return $_POST;
        }

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (is_array($data)) {
            return $data;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && empty($_FILES)) {
            $this->respond(400, false, 'Format data tidak valid. Gunakan FormData atau JSON.');
        }

        return [];
    }

    private function validate(array $input, ?int $id = null): array
    {
        $nim = trim((string) ($input['nim'] ?? ''));
        $nama = trim((string) ($input['nama'] ?? ''));
        $prodi = trim((string) ($input['prodi'] ?? ''));
        $status = strtolower(trim((string) ($input['status'] ?? '')));
        $email = trim((string) ($input['email'] ?? ''));
        $dosen = $input['dosen_id'] ?? null;

        if ($nim === '' || $nama === '' || $prodi === '' || $status === '') {
            $this->respond(422, false, 'NIM, nama, prodi, dan status wajib diisi.');
        }

        // Kolom nim pada database adalah VARCHAR(20), jadi tidak dibatasi 9 digit.
        if (strlen($nim) > 20) {
            $this->respond(422, false, 'NIM maksimal 20 karakter.');
        }

        if (!in_array($status, ['aktif', 'cuti', 'lulus'], true)) {
            $this->respond(422, false, 'Status harus aktif, cuti, atau lulus.');
        }

        // Kolom email pada database boleh NULL, sehingga email boleh dikosongkan.
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->respond(422, false, 'Format email tidak valid.');
        }

        if ($this->model->nimExists($nim, $id)) {
            $this->respond(409, false, 'NIM sudah terdaftar.');
        }

        return [
            'nim' => $nim,
            'nama' => $nama,
            'prodi' => $prodi,
            'status' => $status,
            'email' => $email === '' ? null : $email,
            'dosen_id' => ($dosen === '' || $dosen === null) ? null : (int) $dosen,
        ];
    }

    private function uploadPhoto(): ?string
    {
        if (!isset($_FILES['foto']) || !is_array($_FILES['foto'])) {
            return null;
        }

        $file = $_FILES['foto'];

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->respond(422, false, 'Upload foto gagal. Silakan pilih ulang file.');
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        $originalName = (string) ($file['name'] ?? '');
        $size = (int) ($file['size'] ?? 0);

        $isManualTemp = !empty($file['manual_tmp']) && is_file($tmpName);
        if ($tmpName === '' || (!is_uploaded_file($tmpName) && !$isManualTemp)) {
            $this->respond(422, false, 'File upload tidak valid.');
        }

        if ($size <= 0 || $size > self::MAX_FILE_SIZE) {
            $this->respond(422, false, 'Ukuran foto maksimal 2 MB.');
        }

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            $this->respond(422, false, 'Format foto harus JPG, JPEG, atau PNG.');
        }

        if (function_exists('exif_imagetype')) {
            $imageType = @exif_imagetype($tmpName);
        } else {
            $info = @getimagesize($tmpName);
            $imageType = $info[2] ?? false;
        }
        $validSignature = (
            ($extension === 'jpg' || $extension === 'jpeg') && $imageType === IMAGETYPE_JPEG
        ) || (
            $extension === 'png' && $imageType === IMAGETYPE_PNG
        );

        if (!$validSignature) {
            $this->respond(422, false, 'Isi file bukan gambar JPG atau PNG yang valid.');
        }

        $uploadDir = __DIR__ . '/../../uploads';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            $this->respond(500, false, 'Folder penyimpanan foto tidak dapat dibuat.');
        }

        try {
            $newName = 'foto_' . bin2hex(random_bytes(10)) . '.' . $extension;
        } catch (Throwable $e) {
            $newName = uniqid('foto_', true) . '.' . $extension;
        }

        $destination = $uploadDir . '/' . $newName;

        $moved = $isManualTemp ? @rename($tmpName, $destination) : move_uploaded_file($tmpName, $destination);
        if (!$moved) {
            $this->respond(500, false, 'Foto gagal dipindahkan ke folder penyimpanan.');
        }

        return $newName;
    }

    private function deletePhotoFile(?string $filename): void
    {
        if (!$filename) {
            return;
        }

        // Hanya nama file, bukan path bebas, yang boleh dihapus.
        $safeName = basename($filename);
        if ($safeName !== $filename) {
            return;
        }

        $path = __DIR__ . '/../../uploads/' . $safeName;
        if (is_file($path)) {
            @unlink($path);
        }
    }

    public function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $id = null;

        if (isset($_GET['id'])) {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            if (!$id || $id < 1) {
                $this->respond(400, false, 'ID mahasiswa tidak valid.');
            }
        }

        try {
            if ($method === 'GET') {
                if ($id !== null) {
                    $student = $this->model->find($id);
                    if (!$student) {
                        $this->respond(404, false, 'Mahasiswa tidak ditemukan.');
                    }
                    $this->respond(200, true, 'Data mahasiswa berhasil diambil.', $student);
                }

                $this->respond(200, true, 'Data mahasiswa berhasil diambil.', $this->model->getAll());
            }

            // POST tanpa ID untuk tambah, POST dengan ID untuk edit FormData.
            if ($method === 'POST') {
                Auth::requireAdmin();
                $input = $this->readInput();
                $data = $this->validate($input, $id);

                if ($id === null) {
                    $newPhoto = $this->uploadPhoto();
                    $data['foto'] = $newPhoto;

                    try {
                        $newId = $this->model->create($data);
                    } catch (Throwable $e) {
                        $this->deletePhotoFile($newPhoto);
                        throw $e;
                    }

                    $this->respond(201, true, 'Mahasiswa berhasil ditambahkan.', $this->model->find($newId));
                }

                $current = $this->model->find($id);
                if (!$current) {
                    $this->respond(404, false, 'Mahasiswa tidak ditemukan.');
                }

                $newPhoto = $this->uploadPhoto();
                $data['foto'] = $newPhoto ?? $current['foto'];

                try {
                    $this->model->update($id, $data);
                } catch (Throwable $e) {
                    if ($newPhoto !== null) {
                        $this->deletePhotoFile($newPhoto);
                    }
                    throw $e;
                }

                // Foto lama dihapus setelah data baru berhasil disimpan.
                if ($newPhoto !== null && $current['foto'] !== $newPhoto) {
                    $this->deletePhotoFile($current['foto']);
                }

                $this->respond(200, true, 'Data mahasiswa berhasil diperbarui.', $this->model->find($id));
            }

            // Kompatibilitas untuk pembaruan JSON dari client lama.
            if ($method === 'PUT' || $method === 'PATCH') {
                Auth::requireAdmin();

                if ($id === null) {
                    $this->respond(400, false, 'Parameter id wajib diisi, contoh ?id=1.');
                }

                $current = $this->model->find($id);
                if (!$current) {
                    $this->respond(404, false, 'Mahasiswa tidak ditemukan.');
                }

                $input = $this->readInput();
                if ($method === 'PATCH') {
                    $input = array_merge($current, $input);
                }

                $data = $this->validate($input, $id);
                $newPhoto = $method === 'PUT' ? $this->uploadPhoto() : null;
                $data['foto'] = $newPhoto ?? $current['foto'];

                try {
                    $this->model->update($id, $data);
                } catch (Throwable $e) {
                    if ($newPhoto !== null) {
                        $this->deletePhotoFile($newPhoto);
                    }
                    throw $e;
                }

                if ($newPhoto !== null && $current['foto'] !== $newPhoto) {
                    $this->deletePhotoFile($current['foto']);
                }

                $this->respond(200, true, 'Data mahasiswa berhasil diperbarui.', $this->model->find($id));
            }

            if ($method === 'DELETE') {
                Auth::requireAdmin();

                if ($id === null) {
                    $this->respond(400, false, 'Parameter id wajib diisi, contoh ?id=1.');
                }

                $current = $this->model->find($id);
                if (!$current) {
                    $this->respond(404, false, 'Mahasiswa tidak ditemukan.');
                }

                if ($this->model->delete($id)) {
                    $this->deletePhotoFile($current['foto']);
                    $this->respond(200, true, 'Data mahasiswa dan foto terkait berhasil dihapus.');
                }

                $this->respond(500, false, 'Data mahasiswa gagal dihapus.');
            }

            header('Allow: GET, POST, PUT, PATCH, DELETE');
            $this->respond(405, false, 'Method tidak diizinkan.');
        } catch (Throwable $e) {
            error_log('[Acara 27 API] ' . $e->getMessage());
            $this->respond(500, false, 'Terjadi kesalahan server. Periksa storage/logs/php-error.log.');
        }
    }
}
