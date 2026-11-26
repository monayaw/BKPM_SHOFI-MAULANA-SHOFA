<?php

class Mahasiswa
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        $sql = 'SELECT id, nim, nama, prodi, status, email, dosen_id, foto
                FROM mahasiswa ORDER BY id DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nim, nama, prodi, status, email, dosen_id, foto
             FROM mahasiswa WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function nimExists(string $nim, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM mahasiswa WHERE nim = :nim';
        $params = [':nim' => $nim];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params[':id'] = $exceptId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO mahasiswa
                (nim, nama, prodi, status, email, dosen_id, foto)
                VALUES (:nim, :nama, :prodi, :status, :email, :dosen_id, :foto)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nim' => $data['nim'],
            ':nama' => $data['nama'],
            ':prodi' => $data['prodi'],
            ':status' => $data['status'],
            ':email' => $data['email'],
            ':dosen_id' => $data['dosen_id'],
            ':foto' => $data['foto'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE mahasiswa
             SET nim = :nim, nama = :nama, prodi = :prodi,
                 status = :status, email = :email, dosen_id = :dosen_id,
                 foto = :foto
             WHERE id = :id'
        );

        $stmt->execute([
            ':nim' => $data['nim'],
            ':nama' => $data['nama'],
            ':prodi' => $data['prodi'],
            ':status' => $data['status'],
            ':email' => $data['email'],
            ':dosen_id' => $data['dosen_id'],
            ':foto' => $data['foto'] ?? null,
            ':id' => $id,
        ]);

        return $stmt->rowCount() > 0 || $this->find($id) !== null;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM mahasiswa WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
