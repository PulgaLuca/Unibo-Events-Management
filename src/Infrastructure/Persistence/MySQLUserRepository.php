<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Repositories\UserRepositoryInterface;
use App\Domain\Entities\User;
use App\Infrastructure\Database\DatabaseConnection;
use mysqli;

class MySQLUserRepository implements UserRepositoryInterface 
{
    private mysqli $conn;

    public function __construct() {
        $this->conn = DatabaseConnection::getConnection();
    }

    public function findByEmail(string $email): ?User {
        $stmt = $this->conn->prepare("SELECT * FROM USER WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        return $data ? $this->mapToUser($data) : null;
    }

    public function findById(string $id): ?User {
        $stmt = $this->conn->prepare("SELECT * FROM USER WHERE user_id = ?");
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        return $data ? $this->mapToUser($data) : null;
    }

    public function save(User $user): void {
        $stmt = $this->conn->prepare(
        "INSERT INTO USER (user_id, nome, cognome, email, password_hash, is_admin, is_professor, is_mentor)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            'sssssiib',
            $user->id,
            $user->nome,
            $user->cognome,
            $user->email,
            $user->passwordHash,
            $user->isAdmin,
            $user->isProfessor,
            $user->isMentor
        );
        $stmt->execute();
    }

    public function updateLastLogin(string $id): void {
        $stmt = $this->conn->prepare("UPDATE USER SET ultimo_accesso = CURRENT_TIMESTAMP WHERE user_id = ?");
        $stmt->bind_param('s', $id);
        $stmt->execute();
    }

    private function mapToUser(array $data): User {
        return new User(
            $data['user_id'],
            $data['nome'],
            $data['cognome'],
            $data['email'],
            $data['password_hash'],
            (bool)$data['is_admin'],
            (bool)$data['is_professor'],
            (bool)$data['is_mentor'],
            $data['ultimo_accesso']
        );
    }
}
?>