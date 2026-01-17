<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mysql\Events;

use App\Domain\Entities\Events\Event;
use App\Domain\Entities\Events\EventStatus;
use App\Domain\Repositories\Events\IEventRepository;
use \App\Domain\Entities\Events\Location;
use \App\Domain\Repositories\Location\ILocationRepository;

use DateTime;
use PDO;

class EventRepository implements IEventRepository
{
    private ILocationRepository $locationRepository;
    private PDO $connection;

    public function __construct(PDO $pdoConnection, ILocationRepository $locationRepository)
    {
        $this->connection = $pdoConnection;
        $this->locationRepository = $locationRepository;
    }

    public function save(Event $event): void
    {
        $sql = <<<SQL
            INSERT INTO EVENT (
                id, title, description, start_date, end_date, image_url,
                location_id, url, registration_deadline,
                min_participants, max_participants,
                status, type_id, participation_type_id,
                creator_user_id
            ) VALUES (
                :id, :title, :description, :start_date, :end_date, :image_url,
                :location_id, :url, :registration_deadline,
                :min_participants, :max_participants,
                :status, :type_id, :participation_type_id,
                :creator_user_id
            )
        SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($this->mapToDatabase($event));
    }

    public function update(Event $event): void
    {
        $sql = <<<SQL
            UPDATE EVENT SET
                title = :title,
                description = :description,
                start_date = :start_date,
                end_date = :end_date,
                image_url = :image_url,
                location_id = :location_id,
                url = :url,
                registration_deadline = :registration_deadline,
                min_participants = :min_participants,
                max_participants = :max_participants,
                status = :status
            WHERE id = :id
        SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($this->mapToDatabaseUpdate($event));
    }


    public function delete(string $eventId): void
    {
        $stmt = $this->connection->prepare('DELETE FROM EVENT WHERE id = :id');

        $stmt->execute(['id' => $eventId]);
    }

    public function findById(string $eventId): ?Event
    {
        $stmt = $this->connection->prepare(
            "SELECT * FROM EVENT WHERE id = :id"
        );
        $stmt->execute(['id' => $eventId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $location = null;
        if ($row['location_id']) {
            $location = $this->locationRepository->findById($row['location_id']);
        }

        return new Event(
            $row['id'],
            $row['title'],
            $row['description'],
            new \DateTime($row['start_date']),
            $row['end_date'] ? new \DateTime($row['end_date']) : null,
            $row['image_url'],
            $location,
            $row['url'],
            $row['registration_deadline'] ? new \DateTime($row['registration_deadline']) : null,
            (int)$row['min_participants'],
            $row['max_participants'] !== null ? (int)$row['max_participants'] : null,
            EventStatus::fromString($row['status']),
            $row['type_id'],
            $row['participation_type_id'],
            $row['creator_user_id']
        );
    }

    public function findAll(): array
    {
        $sql = <<<SQL
            SELECT 
                e.*,
                l.id AS location_id,
                l.country AS location_country,
                l.city AS location_city,
                l.description AS location_description
            FROM EVENT e
            LEFT JOIN LOCATION l ON l.id = e.location_id
            ORDER BY e.start_date DESC
        SQL;

        $stmt = $this->connection->query($sql);

        $events = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $events[] = $this->mapToEntity($row);
        }

        return $events;
    }

    public function createParticipation(array $data): void
    {
        $stmt = $this->connection->prepare(
            "INSERT INTO EVENT_PARTICIPATION 
            (id, event_id, user_id, team_id, role, registration_date) 
            VALUES (:id, :event_id, :user_id, :team_id, :role, :registration_date)"
        );
        
        $stmt->execute($data);
    }

    public function deleteParticipation(string $eventId, int $userId): void
    {
        $stmt = $this->connection->prepare(
            "DELETE FROM EVENT_PARTICIPATION 
            WHERE event_id = :event_id AND user_id = :user_id"
        );
        
        $stmt->execute([
            'event_id' => $eventId,
            'user_id' => $userId
        ]);
    }

    public function getUserEventRole(string $eventId, int $userId): ?string
    {
        $stmt = $this->connection->prepare(
            "SELECT role 
            FROM EVENT_PARTICIPATION 
            WHERE event_id = :event_id AND user_id = :user_id"
        );

        $stmt->execute([
            'event_id' => $eventId,
            'user_id' => $userId
        ]);

        $role = $stmt->fetchColumn();
        return $role !== false ? $role : null;
    }


    public function checkParticipation(string $eventId, int $userId): bool
    {
        $stmt = $this->connection->prepare(
            "SELECT COUNT(*) as count FROM EVENT_PARTICIPATION 
            WHERE event_id = :event_id AND user_id = :user_id"
        );
        
        $stmt->execute([
            'event_id' => $eventId,
            'user_id' => $userId
        ]);
        
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    public function getEventParticipants(string $eventId): array
    {
        $stmt = $this->connection->prepare(
            "SELECT 
                u.id,
                u.first_name,
                u.last_name,
                u.email,
                ep.role,
                ep.registration_date
            FROM EVENT_PARTICIPATION ep
            JOIN USERS u ON u.id = ep.user_id
            WHERE ep.event_id = :event_id
            AND ep.user_id IS NOT NULL"
        );

        $stmt->execute(['event_id' => $eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventTeamsWithMembers(string $eventId): array
    {
        $stmt = $this->connection->prepare(
            "SELECT
                t.team_id,
                t.name AS team_name,
                u.user_id,
                u.first_name,
                u.last_name,
                tm.role AS team_role
            FROM EVENT_PARTICIPATION ep
            JOIN TEAM t ON t.team_id = ep.team_id
            JOIN TEAM_MEMBERSHIP tm ON tm.team_id = t.team_id
            JOIN USER u ON u.user_id = tm.user_id
            WHERE ep.event_id = :event_id
            AND tm.request_status = 'Accepted'"
        );

        $stmt->execute(['event_id' => $eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findEventsCreatedByUser(int $userId): array
    {
        $stmt = $this->connection->prepare(
            "SELECT * FROM EVENT WHERE creator_user_id = :user_id"
        );

        $stmt->execute(['user_id' => $userId]);

        return array_map(
            fn($row) => $this->mapToEntity($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function getRequiredSkills(string $eventId): array
    {
        $stmt = $this->connection->prepare(
            "SELECT s.skill_id, s.name
            FROM EVENT_REQUIRED_SKILL ers
            JOIN SKILL s ON s.skill_id = ers.skill_id
            WHERE ers.event_id = :event_id"
        );

        $stmt->execute(['event_id' => $eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByFilters(array $filters): array
    {
        $sql = "SELECT e.*, l.id AS location_id, l.country AS location_country, l.city AS location_city, l.description AS location_description
                FROM EVENT e
                LEFT JOIN LOCATION l ON e.location_id = l.id";

        $conditions = [];
        $params = [];

        if (!empty($filters['q'])) {
            $conditions[] = "(e.title LIKE :q OR e.description LIKE :q)";
            $params[':q'] = "%{$filters['q']}%";
        }

        if (!empty($filters['country'])) {
            $conditions[] = "l.country = :country";
            $params[':country'] = $filters['country'];
        }

        if (!empty($filters['city'])) {
            $conditions[] = "l.city = :city";
            $params[':city'] = $filters['city'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = "e.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['participationType'])) {
            $conditions[] = "e.participation_type_id = :participationType";
            $params[':participationType'] = $filters['participationType'];
        }

        if (!empty($filters['startDate'])) {
            $conditions[] = "e.start_date >= :startDate";
            $params[':startDate'] = $filters['startDate'];
        }

        if (!empty($filters['endDate'])) {
            $conditions[] = "e.end_date <= :endDate";
            $params[':endDate'] = $filters['endDate'];
        }

        if (!empty($filters['registrationDeadline'])) {
            $conditions[] = "e.registration_deadline <= :registrationDeadline";
            $params[':registrationDeadline'] = $filters['registrationDeadline'];
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY e.start_date DESC";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);

        $events = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $events[] = $this->mapToEntity($row);
        }
        
        return $events;
    }

    /**
     * Map DB row -> Domain Entity
     */
    private function mapToEntity(array $row): Event
    {

        error_log(print_r($row, true));
        $location = null;

        if (!empty($row['location_id'])) {
            $location = new Location(
                $row['location_id'],
                $row['location_country'] ?? '',
                $row['location_city'] ?? '',
                $row['location_description'] ?? ''
            );
        }


        return new Event(
            $row['id'],
            $row['title'],
            $row['description'],
            new DateTime($row['start_date']),
            $row['end_date'] ? new DateTime($row['end_date']) : null,
            $row['image_url'],
            $location,
            $row['url'],
            $row['registration_deadline'] ? new DateTime($row['registration_deadline']) : null,
            (int) $row['min_participants'],
            $row['max_participants'] !== null ? (int) $row['max_participants'] : null,
            EventStatus::fromString($row['status']),
            $row['type_id'],
            $row['participation_type_id'],
            $row['creator_user_id']
        );
    }

    /**
     * Map Domain Entity -> DB array
     */
    private function mapToDatabase(Event $event): array
    {
        return [
            'id' => $event->getEventId(),
            'title' => $event->getTitle(),
            'description' => $event->getDescription(),
            'start_date' => $event->getStartDate()->format('Y-m-d H:i:s'),
            'end_date' => $event->getEndDate()?->format('Y-m-d H:i:s'),
            'image_url' => $event->getImageUrl(),
            'location_id' => $event->getLocation()->getId(),
            'url' => $event->getUrl(),
            'registration_deadline' => $event->getRegistrationDeadline()?->format('Y-m-d H:i:s'),
            'min_participants' => $event->getMinParticipants(),
            'max_participants' => $event->getMaxParticipants(),
            'status' => $event->getStatus()->value,
            'type_id' => $event->getTypeId(),
            'participation_type_id' => $event->getParticipationTypeId(),
            'creator_user_id' => $event->getCreatorUserId()
        ];
    }

    private function mapToDatabaseUpdate(Event $event): array
    {
        return [
            'id' => $event->getEventId(),
            'title' => $event->getTitle(),
            'description' => $event->getDescription(),
            'start_date' => $event->getStartDate()->format('Y-m-d H:i:s'),
            'end_date' => $event->getEndDate()?->format('Y-m-d H:i:s'),
            'image_url' => $event->getImageUrl(),
            'location_id' => $event->getLocation()->getId(),
            'url' => $event->getUrl(),
            'registration_deadline' => $event->getRegistrationDeadline()?->format('Y-m-d H:i:s'),
            'min_participants' => $event->getMinParticipants(),
            'max_participants' => $event->getMaxParticipants(),
            'status' => $event->getStatus()->value
        ];
    }

    private function mapRowsToEvents(array $rows): array
    {
        return array_map(fn($row) => $this->mapToEntity($row), $rows);
    }

    private function baseSelect(): string
    {
        return "
            SELECT DISTINCT
                e.*,
                l.id AS location_id,
                l.country AS location_country,
                l.city AS location_city,
                l.description AS location_description
            FROM EVENT e
            LEFT JOIN LOCATION l ON l.id = e.location_id
        ";
    }

    // PRESET FILTERS
    public function findMyUpcomingEvents(int $userId): array
    {
        $sql = $this->baseSelect() . "
            LEFT JOIN EVENT_PARTICIPATION s ON s.event_id = e.id
            WHERE (e.creator_user_id = :userId OR s.user_id = :userId)
            AND e.start_date >= NOW()
            ORDER BY e.start_date ASC
        ";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['userId' => $userId]);

        return $this->mapRowsToEvents($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findHostedByUser(int $userId): array
    {
        $sql = $this->baseSelect() . "
            WHERE e.creator_user_id = :userId
            ORDER BY e.start_date DESC
        ";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['userId' => $userId]);

        return $this->mapRowsToEvents($stmt->fetchAll(PDO::FETCH_ASSOC));
    }


    public function findTrendingEvents(): array
    {
        $sql = $this->baseSelect() . "
            LEFT JOIN EVENT_PARTICIPATION s ON s.event_id = e.id
            GROUP BY e.id
            ORDER BY COUNT(s.user_id) DESC
            LIMIT 20
        ";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        return $this->mapRowsToEvents($stmt->fetchAll(PDO::FETCH_ASSOC));
    }


    public function findUpcomingEvents(): array
    {
        $sql = $this->baseSelect() . "
            WHERE e.start_date >= NOW()
            ORDER BY e.start_date ASC
        ";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        return $this->mapRowsToEvents($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function findPastEvents(): array
    {
        $sql = $this->baseSelect() . "
            WHERE e.start_date < NOW()
            ORDER BY e.start_date DESC
        ";

        $stmt = $this->connection->prepare($sql);
        $stmt->execute();

        return $this->mapRowsToEvents($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
