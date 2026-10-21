
<?php

require_once __DIR__ . '/../Core/Database.php';

class Buku
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        $query = $this->db->query(
            'SELECT * FROM buku ORDER BY id DESC'
        );

        return $query->fetchAll();
    }

    public function find(int $id): array|false
    {
        $query = $this->db->prepare(
            'SELECT * FROM buku WHERE id = ?'
        );

        $query->execute([$id]);

        return $query->fetch();
    }

    public function create(array $data): int
    {
        $query = $this->db->prepare(
            'INSERT INTO buku (judul, penulis, tahun)
             VALUES (?, ?, ?)'
        );

        $query->execute([
            $data['judul'],
            $data['penulis'],
            $data['tahun'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $query = $this->db->prepare(
            'UPDATE buku
             SET judul = ?, penulis = ?, tahun = ?
             WHERE id = ?'
        );

        return $query->execute([
            $data['judul'],
            $data['penulis'],
            $data['tahun'],
            $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $query = $this->db->prepare(
            'DELETE FROM buku WHERE id = ?'
        );

        $query->execute([$id]);

        return $query->rowCount() > 0;
    }
}
