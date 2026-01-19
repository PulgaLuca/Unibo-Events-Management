<?php

declare(strict_types=1);

namespace App\Domain\Repositories\Team;

use App\Domain\Entities\Team\Team;
use App\Domain\Entities\Team\TeamMembership;

interface ITeamRepository
{
    // Team CRUD operations
    public function findById(string $teamId): ?Team;
    public function create(Team $team): Team;
    public function update(Team $team): bool;
    public function delete(string $teamId): bool;
    public function getAll(): array;
    public function getSearchingTeams(): array;
    
    // Team membership operations
    public function addMember(string $teamId, int $userId, string $status = TeamMembership::STATUS_PENDING): bool;
    public function removeMember(string $teamId, int $userId): bool;
    public function updateMemberStatus(string $teamId, int $userId, string $status): bool;
    public function getMembership(string $teamId, int $userId): ?TeamMembership;
    public function getTeamMembers(string $teamId, ?string $status = null): array;
    public function getUserTeams(int $userId, ?string $memberStatus = null): array;
    public function getPendingRequests(string $teamId): array;
    
    // Team status management
    public function getMemberCount(string $teamId, bool $includeLeader = true): int;
    public function updateTeamStatus(string $teamId, string $status): bool;
    public function getTeamLeaders(string $teamId): array;
    public function hasLeader(string $teamId): bool;
    public function isUserInTeam(string $teamId, int $userId): bool;
    public function isUserLeader(string $teamId, int $userId): bool;
}
