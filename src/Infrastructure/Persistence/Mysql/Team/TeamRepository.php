<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mysql\Team;

use PDO;
use Datetime;
use App\Domain\Entities\Team\Team;
use App\Domain\Repositories\Team\ITeamRepository;

class TeamRepository implements ITeamRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Crea un nuovo team
     */
    public function create(Team $team): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO team 
            (id, name, description, status, min_participants, max_participants, mentor_id)
            VALUES (:id, :name, :description, :status, :min, :max, :mentor)
        ");

        $stmt->execute([
            ':id' => $team->id,
            ':name' => $team->name,
            ':description' => $team->description,
            ':status' => 'Searching',
            ':min' => $team->min,
            ':max' => $team->max,
        ]);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM TEAM');

        $teams = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $teams[] = $this->mapToEntity($row);
        }

        return $teams;
    }


    /**
     * Ritorna i team di cui l'utente fa parte
     */
    public function findByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT t.*
            FROM team t
            INNER JOIN team_members tm ON tm.team_id = t.id
            WHERE tm.user_id = :user_id
        ");

        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Ritorna un team per ID
     */
    public function findById(string $id): ?object
    {
        $stmt = $this->pdo->prepare("SELECT * FROM team WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $team = $stmt->fetch(PDO::FETCH_OBJ);
        return $team ?: null;
    }

    /**
     * Conta i membri di un team
     */
    public function countMembers(string $teamId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM team_members 
            WHERE team_id = :team_id
        ");
        $stmt->execute([':team_id' => $teamId]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Aggiunge un utente a un team
     */
    public function addMember(string $teamId, int $userId): void
    {
        // Evita doppioni
        $stmt = $this->pdo->prepare("
            SELECT 1 
            FROM team_members 
            WHERE team_id = :team_id AND user_id = :user_id
        ");
        $stmt->execute([
            ':team_id' => $teamId,
            ':user_id' => $userId
        ]);

        if ($stmt->fetch()) {
            // throw new DomainException('Sei già membro di questo team');
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO team_members (team_id, user_id, joined_at)
            VALUES (:team_id, :user_id, NOW())
        ");

        $stmt->execute([
            ':team_id' => $teamId,
            ':user_id' => $userId
        ]);
    }

    private function mapToEntity(array $row): Team
    {
        return new Team(
            $row['id'],
            $row['name'],
            $row['description'],
            $row['status'],
            $row['min_participants'],
            $row['max_participants'],
            new DateTime($row['created_at'])
        );
    }
}
