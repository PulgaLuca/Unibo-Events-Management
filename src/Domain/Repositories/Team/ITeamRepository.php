<?php

declare(strict_types=1);

namespace App\Domain\Repositories\Team;

use App\Domain\Entities\Team\Team;

interface ITeamRepository
{
    public function create(Team $team): void;

    public function findByUserId(int $userId): array;

    public function findAll(): array;

    public function findById(string $id): ?object;

    public function countMembers(string $teamId): int;

    public function addMember(string $teamId, int $userId): void;
}
