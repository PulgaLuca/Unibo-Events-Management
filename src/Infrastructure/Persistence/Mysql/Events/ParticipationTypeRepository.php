<?php

namespace App\Infrastructure\Persistence\Mysql\Events;

use App\Domain\Repositories\Events\IParticipationTypeRepository;
use PDO;

class ParticipationTypeRepository implements IParticipationTypeRepository
{
    private PDO $pdo;

    public function __construct(PDO $connection)
    {
        $this->pdo = $connection;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT type_id AS id, name FROM participation_type ORDER BY name');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
