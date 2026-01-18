<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mysql\Skill;

use App\Domain\Entities\Skill\Skill;
use App\Domain\Repositories\Skill\ISkillRepository;
use PDO;

class SkillRepository implements ISkillRepository
{
    private PDO $pdo;
    private string $table = 'skills';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById(int $skillId): ?Skill
    {
        $query = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$skillId]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, Skill::class);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findByName(string $name): ?Skill
    {
        $query = "SELECT * FROM {$this->table} WHERE name = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$name]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, Skill::class);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findAll(): array
    {
        $query = "SELECT * FROM {$this->table} ORDER BY category, name";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, Skill::class);
    }

    public function findByCategory(string $category): array
    {
        $query = "SELECT * FROM {$this->table} WHERE category = ? ORDER BY name";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$category]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, Skill::class);
    }

    public function create(Skill $skill): Skill
    {
        $query = "INSERT INTO {$this->table} (name, category, created_at, updated_at) 
                  VALUES (?, ?, NOW(), NOW())";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            $skill->name,
            $skill->category,
        ]);

        $skillId = (int) $this->pdo->lastInsertId();
        $skill->id = $skillId;
        return $skill;
    }

    public function update(Skill $skill): bool
    {
        $query = "UPDATE {$this->table} SET name = ?, category = ?, updated_at = NOW() 
                  WHERE id = ?";
        
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([
            $skill->name,
            $skill->category,
            $skill->id,
        ]);
    }

    public function delete(int $skillId): bool
    {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$skillId]);
    }

    public function findOrCreateByName(string $name): int
    {
        $query = "SELECT id FROM {$this->table} WHERE LOWER(name) = LOWER(?)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$name]);

        $id = $stmt->fetchColumn();
        if ($id !== false) {
            return (int) $id;
        }

        $query = "INSERT INTO {$this->table} (name, category, created_at, updated_at)
                VALUES (?, ?, NOW(), NOW())";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            $name,
            'General',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function getRequiredSkills(string $eventId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT s.id, s.name
            FROM skills s
            JOIN event_required_skill ers ON ers.skill_id = s.id
            WHERE ers.event_id = :event_id"
        );

        $stmt->execute(['event_id' => $eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}
