<?php
class Mahasiswa {
    private PDO $db;
    private array $columns = ['nim', 'nama', 'prodi', 'status', 'email', 'dosen_id'];

    public function __construct() { $this->db = Database::getConnection(); }

    public function getAll(): array {
        return $this->db->query('SELECT id, nim, nama, prodi, status, email, dosen_id FROM mahasiswa ORDER BY id DESC')->fetchAll();
    }
    public function find(int $id): ?array {
        $stmt = $this->db->prepare('SELECT id, nim, nama, prodi, status, email, dosen_id FROM mahasiswa WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
    public function nimExists(string $nim, ?int $exceptId = null): bool {
        $sql = 'SELECT COUNT(*) FROM mahasiswa WHERE nim = :nim';
        $params = ['nim' => $nim];
        if ($exceptId !== null) { $sql .= ' AND id <> :id'; $params['id'] = $exceptId; }
        $stmt = $this->db->prepare($sql); $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }
    public function create(array $data): int {
        $sql = 'INSERT INTO mahasiswa (nim, nama, prodi, status, email, dosen_id) VALUES (:nim, :nama, :prodi, :status, :email, :dosen_id)';
        $stmt = $this->db->prepare($sql); $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }
    public function update(int $id, array $data): bool {
        $data['id'] = $id;
        $stmt = $this->db->prepare('UPDATE mahasiswa SET nim=:nim, nama=:nama, prodi=:prodi, status=:status, email=:email, dosen_id=:dosen_id WHERE id=:id');
        $stmt->execute($data);
        return $stmt->rowCount() > 0 || $this->find($id) !== null;
    }
    public function delete(int $id): bool {
        $stmt = $this->db->prepare('DELETE FROM mahasiswa WHERE id=:id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
