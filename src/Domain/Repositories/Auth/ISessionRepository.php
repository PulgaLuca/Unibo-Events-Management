<?php

declare(strict_types=1);

namespace App\Domain\Repositories\Auth;

use App\Domain\Entities\Auth\Session;

interface ISessionRepository
{
    public function findByToken(string $tokenHash): ?Session;
    public function create(Session $session): Session;
    public function deleteByToken(string $tokenHash): bool;
    public function deleteExpired(): int;
    public function deleteByUserId(int $userId): int;
}
