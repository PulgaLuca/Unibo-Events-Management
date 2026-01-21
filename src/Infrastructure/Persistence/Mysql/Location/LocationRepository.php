<?php

namespace App\Infrastructure\Persistence\Mysql\Location;

use App\Domain\Entities\Events\Location;
use App\Domain\Repositories\Location\ILocationRepository;
use PDO;

class LocationRepository implements ILocationRepository
{
    private PDO $pdo;

    public function __construct(PDO $connection)
    {
        $this->pdo = $connection;
    }

    public function findById(string $id): ?Location
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM LOCATION WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Location(
            $row['id'],
            $row['country'],
            $row['city'],
            $row['description']
        );
    }

    public function create(Location $location): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO LOCATION (id, country, city, description)
             VALUES (:id, :country, :city, :description)"
        );

        $stmt->execute([
            'id' => $location->getId(),
            'country' => $location->getCountry(),
            'city' => $location->getCity(),
            'description' => $location->getDescription()
        ]);
    }

    public function update(Location $location): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE LOCATION
             SET country = :country, city = :city, description = :description
             WHERE id = :id"
        );

        $stmt->execute([
            'id' => $location->getId(),
            'country' => $location->getCountry(),
            'city' => $location->getCity(),
            'description' => $location->getDescription()
        ]);
    }
}
