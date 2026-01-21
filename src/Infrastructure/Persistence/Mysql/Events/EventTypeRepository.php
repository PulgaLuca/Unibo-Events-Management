<?php

namespace App\Infrastructure\Persistence\Mysql\Events;

use App\Domain\Repositories\Events\IEventTypeRepository;
use PDO;

class EventTypeRepository implements IEventTypeRepository
{
    private PDO $pdo;

    public function __construct(PDO $connection)
    {
        $this->pdo = $connection;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT id, name FROM event_type ORDER BY name');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
