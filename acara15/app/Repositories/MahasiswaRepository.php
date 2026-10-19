<?php

require_once __DIR__ . '/../Core/Database.php';
require_once __DIR__ . '/../Models/Mahasiswa.php';

class MahasiswaRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Mengambil semua data mahasiswa
    public function all(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM mahasiswa ORDER BY id DESC"
        );

        return $stmt->fetchAll();
    }

    // Mencari mahasiswa berdasarkan ID
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM mahasiswa WHERE id = :id"
        );

        $stmt->execute([
            'id' => $id
        ]);

        $data = $stmt->fetch();

        return $data ?: null;
    }

    // Memeriksa apakah NIM sudah digunakan
    public function existsByNim(
        string $nim,
        ?int $excludeId = null
    ): bool {
        $sql = "SELECT COUNT(*) FROM mahasiswa
                WHERE nim = :nim";

        $params = [
            'nim' => $nim
        ];

        // Abaikan data mahasiswa sendiri saat update
        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    // Menambahkan data mahasiswa
    public function create(Mahasiswa $mahasiswa): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO mahasiswa
                (nim, nama, prodi, status, email, dosen_id)
            VALUES
                (:nim, :nama, :prodi, :status, :email, :dosen_id)
        ");

        return $stmt->execute([
            'nim' => $mahasiswa->getNim(),
            'nama' => $mahasiswa->getNama(),
            'prodi' => $mahasiswa->getProdi(),
            'status' => $mahasiswa->getStatus(),
            'email' => $mahasiswa->getEmail(),
            'dosen_id' => $mahasiswa->getDosenId()
        ]);
    }

    // Mengubah data mahasiswa
    public function update(
        int $id,
        Mahasiswa $mahasiswa
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE mahasiswa SET
                nim = :nim,
                nama = :nama,
                prodi = :prodi,
                status = :status,
                email = :email,
                dosen_id = :dosen_id
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'nim' => $mahasiswa->getNim(),
            'nama' => $mahasiswa->getNama(),
            'prodi' => $mahasiswa->getProdi(),
            'status' => $mahasiswa->getStatus(),
            'email' => $mahasiswa->getEmail(),
            'dosen_id' => $mahasiswa->getDosenId()
        ]);
    }

    // Menghapus data mahasiswa
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM mahasiswa WHERE id = :id"
        );

        $stmt->execute([
            'id' => $id
        ]);

        // true jika ada baris yang berhasil dihapus
        return $stmt->rowCount() > 0;
    }
}