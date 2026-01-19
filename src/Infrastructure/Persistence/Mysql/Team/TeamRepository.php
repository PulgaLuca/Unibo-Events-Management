<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mysql\Team;

use App\Domain\Entities\Team\Team;
use App\Domain\Entities\Team\TeamMembership;
use App\Domain\Repositories\Team\ITeamRepository;
use PDO;
use Ramsey\Uuid\Uuid;

class TeamRepository implements ITeamRepository
{
    private PDO $pdo;
    private string $teamTable = 'team';
    private string $membershipTable = 'team_membership';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(string $teamId): ?Team
    {
        $query = "SELECT * FROM {$this->teamTable} WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$teamId]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, Team::class);
        return $stmt->fetch() ?: null;
    }

    public function create(Team $team): Team
    {
        $teamId = Uuid::uuid4()->toString();
        
        $query = "INSERT INTO {$this->teamTable} (id, name, description, status, max_participants, min_participants, created_at) 
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            $teamId,
            $team->name,
            $team->description ?? null,
            $team->status ?? Team::STATUS_SEARCHING,
            $team->max_participants,
            $team->min_participants ?? 1,
        ]);

        $team->id = $teamId;
        return $team;
    }

    public function update(Team $team): bool
    {
        $query = "UPDATE {$this->teamTable} 
                  SET name = ?, description = ?, status = ?, max_participants = ?, min_participants = ? 
                  WHERE id = ?";
        
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([
            $team->name,
            $team->description ?? null,
            $team->status,
            $team->max_participants,
            $team->min_participants ?? 1,
            $team->id,
        ]);
    }

    public function delete(string $teamId): bool
    {
        $query = "DELETE FROM {$this->teamTable} WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$teamId]);
    }

    public function getAll(): array
    {
        $query = "SELECT t.*, COUNT(CASE WHEN tm.status IN ('Lead', 'Member') THEN 1 END) as member_count
                  FROM {$this->teamTable} t
                  LEFT JOIN {$this->membershipTable} tm ON t.id = tm.team_id
                  GROUP BY t.id
                  ORDER BY t.created_at DESC";
        
        $stmt = $this->pdo->query($query);
        $stmt->setFetchMode(PDO::FETCH_CLASS, Team::class);
        return $stmt->fetchAll();
    }

    public function getSearchingTeams(): array
    {
        $query = "SELECT t.*, COUNT(CASE WHEN tm.status IN ('Lead', 'Member') THEN 1 END) as member_count
                  FROM {$this->teamTable} t
                  LEFT JOIN {$this->membershipTable} tm ON t.id = tm.team_id
                  WHERE t.status = ?
                  GROUP BY t.id
                  ORDER BY t.created_at DESC";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([Team::STATUS_SEARCHING]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, Team::class);
        return $stmt->fetchAll();
    }

    public function addMember(string $teamId, int $userId, string $status = TeamMembership::STATUS_PENDING): bool
    {
        $query = "INSERT INTO {$this->membershipTable} (team_id, user_id, status, joined_at) 
                  VALUES (?, ?, ?, NOW())";
        
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$teamId, $userId, $status]);
    }

    public function removeMember(string $teamId, int $userId): bool
    {
        $query = "DELETE FROM {$this->membershipTable} WHERE team_id = ? AND user_id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$teamId, $userId]);
    }

    public function updateMemberStatus(string $teamId, int $userId, string $status): bool
    {
        $query = "UPDATE {$this->membershipTable} SET status = ? WHERE team_id = ? AND user_id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$status, $teamId, $userId]);
    }

    public function getMembership(string $teamId, int $userId): ?TeamMembership
    {
        $query = "SELECT * FROM {$this->membershipTable} WHERE team_id = ? AND user_id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$teamId, $userId]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, TeamMembership::class);
        return $stmt->fetch() ?: null;
    }

    public function getTeamMembers(string $teamId, ?string $status = null): array
    {
        if ($status) {
            $query = "SELECT tm.*, u.id as user_id, u.email, u.first_name, u.last_name 
                      FROM {$this->membershipTable} tm
                      INNER JOIN users u ON tm.user_id = u.id
                      WHERE tm.team_id = ? AND tm.status = ?
                      ORDER BY tm.status DESC, tm.joined_at ASC";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$teamId, $status]);
        } else {
            $query = "SELECT tm.*, u.id as user_id, u.email, u.first_name, u.last_name 
                      FROM {$this->membershipTable} tm
                      INNER JOIN users u ON tm.user_id = u.id
                      WHERE tm.team_id = ?
                      ORDER BY tm.status DESC, tm.joined_at ASC";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$teamId]);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserTeams(int $userId, ?string $memberStatus = null): array
    {
        if ($memberStatus) {
            $query = "SELECT t.*, tm.status as membership_status, tm.joined_at
                      FROM {$this->teamTable} t
                      INNER JOIN {$this->membershipTable} tm ON t.id = tm.team_id
                      WHERE tm.user_id = ? AND tm.status = ?
                      ORDER BY tm.joined_at DESC";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$userId, $memberStatus]);
        } else {
            $query = "SELECT t.*, tm.status as membership_status, tm.joined_at
                      FROM {$this->teamTable} t
                      INNER JOIN {$this->membershipTable} tm ON t.id = tm.team_id
                      WHERE tm.user_id = ?
                      ORDER BY tm.joined_at DESC";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$userId]);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingRequests(string $teamId): array
    {
        return $this->getTeamMembers($teamId, TeamMembership::STATUS_PENDING);
    }

    public function getMemberCount(string $teamId, bool $includeLeader = true): int
    {
        if ($includeLeader) {
            $query = "SELECT COUNT(*) FROM {$this->membershipTable} 
                      WHERE team_id = ? AND status IN ('Lead', 'Member')";
        } else {
            $query = "SELECT COUNT(*) FROM {$this->membershipTable} 
                      WHERE team_id = ? AND status = 'Member'";
        }
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$teamId]);
        return (int) $stmt->fetchColumn();
    }

    public function updateTeamStatus(string $teamId, string $status): bool
    {
        $query = "UPDATE {$this->teamTable} SET status = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$status, $teamId]);
    }

    public function getTeamLeaders(string $teamId): array
    {
        $query = "SELECT u.* 
                  FROM users u
                  INNER JOIN {$this->membershipTable} tm ON u.id = tm.user_id
                  WHERE tm.team_id = ? AND tm.status = 'Lead'";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$teamId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function hasLeader(string $teamId): bool
    {
        $query = "SELECT COUNT(*) FROM {$this->membershipTable} 
                  WHERE team_id = ? AND status = 'Lead'";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$teamId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function isUserInTeam(string $teamId, int $userId): bool
    {
        $query = "SELECT COUNT(*) FROM {$this->membershipTable} 
                  WHERE team_id = ? AND user_id = ? AND status IN ('Lead', 'Member')";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$teamId, $userId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function isUserLeader(string $teamId, int $userId): bool
    {
        $query = "SELECT COUNT(*) FROM {$this->membershipTable} 
                  WHERE team_id = ? AND user_id = ? AND status = 'Lead'";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$teamId, $userId]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
