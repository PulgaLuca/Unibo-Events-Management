<?php
namespace Infrastructure\Team;

use PDO;

class TeamRepository {
    public function __construct(private PDO $pdo) {}

    public function create($team) {
        $stmt = $this->pdo->prepare("
            INSERT INTO team 
            (id,name,description,status,min_participants,max_participants,mentor_id)
            VALUES (?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $team->id,
            $team->name,
            $team->description,
            'Searching',
            $team->min,
            $team->max,
            $team->mentorId
        ]);
    }

    public function findAll() {
        return $this->pdo->query("SELECT * FROM team")->fetchAll();
    }

    public function findById($id) {
        $s = $this->pdo->prepare("SELECT * FROM team WHERE id=?");
        $s->execute([$id]);
        return $s->fetch();
    }
}
