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
        $stmt->setFetchMode(PDO::FETCH_CLASS, User::class);
        return $stmt->fetch();
    }

    public function findByEmail(string $email): ?User
    {
        $query = "SELECT * FROM {$this->table} WHERE email = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$email]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, User::class);
        return $stmt->fetch();
    }

    public function create(User $user): User
    {
        $query = "INSERT INTO {$this->table} (email, password, first_name, last_name, role, created_at, updated_at) 
                  VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            $user->email,
            $user->password,
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
        $query = "UPDATE {$this->table} SET email = ?, password = ?, first_name = ?, last_name = ?, role = ?, updated_at = NOW() 
                  WHERE id = ?";
        
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([
            $user->email,
            $user->password,
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

    /**
     * Get all skills for a specific user with their proficiency levels
     * Returns array of ['skill_id', 'skill_name', 'category', 'level']
     */
    public function getUserSkills(int $userId): array
    {
        $query = "SELECT s.id as skill_id, s.name as skill_name, s.category, us.level 
                  FROM user_skills us
                  INNER JOIN skills s ON us.skill_id = s.id
                  WHERE us.user_id = ?
                  ORDER BY s.category, s.name";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add a skill to a user with a specific proficiency level
     * Level: 1 = Beginner, 2 = Intermediate, 3 = Advanced, 4 = Expert
     */
    public function addUserSkill(int $userId, int $skillId, int $level): bool
    {
        $query = "INSERT INTO user_skills (user_id, skill_id, level, created_at, updated_at) 
                  VALUES (?, ?, ?, NOW(), NOW())";
        
        try {
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([$userId, $skillId, $level]);
        } catch (\PDOException $e) {
            //(user already has this skill)
            return false;
        }
    }

    /**
     * Update the proficiency level of a user's skill
     */
    public function updateUserSkill(int $userId, int $skillId, int $level): bool
    {
        $query = "UPDATE user_skills SET level = ?, updated_at = NOW() 
                  WHERE user_id = ? AND skill_id = ?";
        
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$level, $userId, $skillId]);
    }

    /**
     * Remove a skill from a user
     */
    public function removeUserSkill(int $userId, int $skillId): bool
    {
        $query = "DELETE FROM user_skills WHERE user_id = ? AND skill_id = ?";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([$userId, $skillId]);
    }

    /**
     * Replace all user's skills with a new set
     * $skills should be an array of ['skill_id' => level, ...]
     */
    public function setUserSkills(int $userId, array $skills): bool
    {
        try {
            $this->pdo->beginTransaction();
            
            // Remove all existing skills
            $deleteQuery = "DELETE FROM user_skills WHERE user_id = ?";
            $stmt = $this->pdo->prepare($deleteQuery);
            $stmt->execute([$userId]);
            
            // Add new skills
            if (!empty($skills)) {
                $insertQuery = "INSERT INTO user_skills (user_id, skill_id, level, created_at, updated_at) 
                               VALUES (?, ?, ?, NOW(), NOW())";
                $stmt = $this->pdo->prepare($insertQuery);
                
                foreach ($skills as $skillId => $level) {
                    $stmt->execute([$userId, $skillId, $level]);
                }
            }
            
            $this->pdo->commit();
            return true;
        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * Get all users with their last session info
     */
    public function getAllWithLastSession(): array
    {
        $query = "SELECT u.*, 
                         s.expires_at as last_session,
                         CASE WHEN s.expires_at > NOW() THEN 1 ELSE 0 END as has_active_session
                  FROM {$this->table} u
                  LEFT JOIN (
                      SELECT user_id, MAX(expires_at) as expires_at
                      FROM sessions
                      GROUP BY user_id
                  ) s ON u.id = s.user_id
                  ORDER BY u.created_at DESC";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get statistics for admin dashboard
     */
    public function getStatistics(): array
    {
        $stats = [];
        
        // Total users
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM {$this->table}");
        $stats['total_users'] = $stmt->fetchColumn();
        
        // Users with skills (completed profile)
        $stmt = $this->pdo->query("SELECT COUNT(DISTINCT user_id) as total FROM user_skills");
        $stats['users_with_skills'] = $stmt->fetchColumn();
        
        // Active sessions
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM sessions WHERE expires_at > NOW()");
        $stats['active_sessions'] = $stmt->fetchColumn();
        
        // Total skills
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM skills");
        $stats['total_skills'] = $stmt->fetchColumn();
        
        // Total events (if events table exists)
        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM events");
            $stats['total_events'] = $stmt->fetchColumn();
        } catch (\PDOException $e) {
            $stats['total_events'] = 0;
        }
        
        return $stats;
    }
}
