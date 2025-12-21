<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mysql\Auth;

use App\Domain\Entities\Auth\User;
use App\Domain\Repositories\Auth\IUserRepository;
use PDO;

class UserRepository implements IUserRepository
{
    private PDO $pdo;
    private string $table = 'users';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $userId): ?User
    {
        $query = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$userId]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'User');
        return $stmt->fetch();
    }

    public function findByEmail(string $email): ?User
    {
        $query = "SELECT * FROM {$this->table} WHERE email = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$email]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'User');
        return $stmt->fetch();
    }

    public function create(User $user): User
    {
        $query = "INSERT INTO {$this->table} (email, password, username, first_name, last_name, role, created_at, updated_at) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            $user->email,
            password_hash($user->password, PASSWORD_BCRYPT),
            $user->username,
            $user->first_name,
            $user->last_name,
            $user->role,
        ]);

        $userId = (int) $this->pdo->lastInsertId();
        $user->id = $userId;
        return $user;
    }

    public function update(User $user): bool
    {
        $query = "UPDATE {$this->table} SET email = ?, password = ?, username = ?, first_name = ?, last_name = ?, role = ?, updated_at = NOW() 
                  WHERE id = ?";
        
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([
            $user->email,
            password_hash($user->password, PASSWORD_BCRYPT),
            $user->username,
            $user->first_name,
            $user->last_name,
            $user->role,
            $user->id,
        ]);
    }

    public function delete(int $userId): bool
    {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$userId]);
    }

    public function existsByEmail(string $email): bool
    {
        $query = "SELECT 1 FROM {$this->table} WHERE email = ? LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$email]);
        return $stmt->rowCount() > 0;
    }
}
