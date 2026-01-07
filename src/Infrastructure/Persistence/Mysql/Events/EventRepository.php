<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Mysql\Events;

use App\Domain\Entities\Events\Event;
use App\Domain\Entities\Events\EventStatus;
use App\Domain\Repositories\Events\IEventRepository;
use DateTime;
use PDO;

class EventRepository implements IEventRepository
{
    private PDO $connection;

    public function __construct(PDO $pdoConnection)
    {
        $this->connection = $pdoConnection;
    }

    public function save(Event $event): void
    {
        $sql = <<<SQL
            INSERT INTO EVENT (
                id, title, description, start_date, end_date, image_url,
                location, url, registration_deadline,
                min_participants, max_participants,
                status, type_id, participation_type_id,
                creator_user_id
            ) VALUES (
                :id, :title, :description, :start_date, :end_date, :image_url,
                :location, :url, :registration_deadline,
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
                location = :location,
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
        $stmt = $this->connection->prepare('SELECT * FROM EVENT WHERE id = :id');

        $stmt->execute(['id' => $eventId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->mapToEntity($row);
    }

    public function findAll(): array
    {
        $stmt = $this->connection->query('SELECT * FROM EVENT ORDER BY start_date DESC');

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
                u.user_id,
                u.first_name,
                u.last_name,
                ep.role,
                ep.registration_date
            FROM EVENT_PARTICIPATION ep
            JOIN USER u ON u.user_id = ep.user_id
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

    public function findEventsJoinedByUser(int $userId): array
    {
        $stmt = $this->connection->prepare(
            "SELECT DISTINCT e.*
            FROM EVENT e
            JOIN EVENT_PARTICIPATION ep ON ep.event_id = e.id
            LEFT JOIN TEAM_MEMBERSHIP tm ON tm.team_id = ep.team_id
            WHERE ep.user_id = :user_id
                OR tm.user_id = :user_id"
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

    /**
     * Map DB row -> Domain Entity
     */
    private function mapToEntity(array $row): Event
    {
        return new Event(
            $row['id'],
            $row['title'],
            $row['description'],
            new DateTime($row['start_date']),
            $row['end_date'] ? new DateTime($row['end_date']) : null,
            $row['image_url'],
            $row['location'],
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
            'location' => $event->getLocation(),
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
            'location' => $event->getLocation(),
            'url' => $event->getUrl(),
            'registration_deadline' => $event->getRegistrationDeadline()?->format('Y-m-d H:i:s'),
            'min_participants' => $event->getMinParticipants(),
            'max_participants' => $event->getMaxParticipants(),
            'status' => $event->getStatus()->value
        ];
    }
}
