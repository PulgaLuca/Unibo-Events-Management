<?php

declare(strict_types=1);

namespace App\Domain\Repositories\Auth;

use App\Domain\Entities\Auth\User;

interface IUserRepository
{
    public function findById(int $userId): ?User;
    public function findByEmail(string $email): ?User;
    public function create(User $user): User;
    public function update(User $user): bool;
    public function delete(int $userId): bool;
    public function existsByEmail(string $email): bool;
}
