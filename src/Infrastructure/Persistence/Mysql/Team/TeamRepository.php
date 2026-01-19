<?php

declare(strict_types=1);

namespace Infrastructure\Team;

use PDO;
use Domain\Team\Team;
use Domain\Exceptions\DomainException;

final class TeamRepository
{
    public function __construct(private PDO $pdo) {}

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
            ':mentor' => $team->mentorId
        ]);

        // Il mentor entra automaticamente nel team
        $this->addMember($team->id, $team->mentorId);
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
    public function findById(int $id): ?object
    {
        $stmt = $this->pdo->prepare("SELECT * FROM team WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $team = $stmt->fetch(PDO::FETCH_OBJ);
        return $team ?: null;
    }

    /**
     * Conta i membri di un team
     */
    public function countMembers(int $teamId): int
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
    public function addMember(int $teamId, int $userId): void
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
            throw new DomainException('Sei già membro di questo team');
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
}
