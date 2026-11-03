<?php

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function create(array $data): int
    {
        // Validasi email sebelum data disimpan (tugas praktik mandiri).
        if (!filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Format email tidak valid.');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO users (username, email, password) VALUES (:username, :email, :password)'
        );
        $stmt->execute([
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password' => $data['password'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, username, email, password, role FROM users WHERE username = :username LIMIT 1'
        );
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function existsByUsernameOrEmail(string $username, string $email): bool
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM users WHERE username = :username OR email = :email'
        );
        $stmt->execute([':username' => $username, ':email' => $email]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
