<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mysql\Auth;

use App\Domain\Entities\Auth\Session;
use App\Domain\Repositories\Auth\ISessionRepository;
use PDO;

class SessionRepository implements ISessionRepository
{
    private PDO $pdo;
    private string $table = 'sessions';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findByToken(string $tokenHash): ?Session
    {
        $query = "SELECT * FROM {$this->table} WHERE token_hash = ? AND expires_at > NOW()";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$tokenHash]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, Session::class);
        return $stmt->fetch();
    }

    public function create(Session $session): Session
    {
        $query = "INSERT INTO {$this->table} (user_id, token_hash, user_agent, expires_at, created_at, updated_at) 
                  VALUES (?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            $session->user_id,
            $session->token_hash,
            $session->user_agent,
            $session->expires_at,
        ]);

        $sessionId = (int) $this->pdo->lastInsertId();
        $session->id = $sessionId;

        return $session;
    }

    public function deleteByToken(string $tokenHash): bool
    {
        $query = "DELETE FROM {$this->table} WHERE token_hash = ?";
        $stmt = $this->pdo->prepare($query);

        return $stmt->execute([$tokenHash]);
    }

    public function deleteExpired(): int
    {
        $query = "DELETE FROM {$this->table} WHERE expires_at <= NOW()";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();

        return $stmt->rowCount();
    }

    public function deleteByUserId(int $userId): int
    {
        $query = "DELETE FROM {$this->table} WHERE user_id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$userId]);

        return $stmt->rowCount();
    }
}
