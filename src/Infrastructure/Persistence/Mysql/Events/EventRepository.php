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
                event_id, title, description, start_date, end_date,
                location, url, registration_deadline,
                min_participants, max_participants,
                status, type_id, participation_type_id,
                creator_user_id, creator_team_id
            ) VALUES (
                :event_id, :title, :description, :start_date, :end_date,
                :location, :url, :registration_deadline,
                :min_participants, :max_participants,
                :status, :type_id, :participation_type_id,
                :creator_user_id, :creator_team_id
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
                location = :location,
                url = :url,
                registration_deadline = :registration_deadline,
                min_participants = :min_participants,
                max_participants = :max_participants,
                status = :status
            WHERE event_id = :event_id
        SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($this->mapToDatabase($event));
    }

    public function delete(string $eventId): void
    {
        $stmt = $this->connection->prepare('DELETE FROM EVENT WHERE event_id = :event_id');

        $stmt->execute(['event_id' => $eventId]);
    }

    public function findById(string $eventId): ?Event
    {
        $stmt = $this->connection->prepare('SELECT * FROM EVENT WHERE event_id = :event_id');

        $stmt->execute(['event_id' => $eventId]);
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

    /**
     * Map DB row → Domain Entity
     */
    private function mapToEntity(array $row): Event
    {
        return new Event(
            $row['event_id'],
            $row['title'],
            $row['description'],
            new DateTime($row['start_date']),
            $row['end_date'] ? new DateTime($row['end_date']) : null,
            $row['location'],
            $row['url'],
            $row['registration_deadline'] ? new DateTime($row['registration_deadline']) : null,
            (int) $row['min_participants'],
            $row['max_participants'] !== null ? (int) $row['max_participants'] : null,
            EventStatus::fromString($row['status']),
            $row['type_id'],
            $row['participation_type_id'],
            $row['creator_user_id'],
            $row['creator_team_id']
        );
    }

    /**
     * Map Domain Entity → DB array
     */
    private function mapToDatabase(Event $event): array
    {
        return [
            'event_id' => $event->getEventId(),
            'title' => $event->getTitle(),
            'description' => $event->getDescription(),
            'start_date' => $event->getStartDate()->format('Y-m-d H:i:s'),
            'end_date' => $event->getEndDate()?->format('Y-m-d H:i:s'),
            'location' => $event->getLocation(),
            'url' => $event->getUrl(),
            'registration_deadline' => $event->getRegistrationDeadline()?->format('Y-m-d H:i:s'),
            'min_participants' => $event->getMinParticipants(),
            'max_participants' => $event->getMaxParticipants(),
            'status' => $event->getStatus()->value,
            'type_id' => $event->getTypeId(),
            'participation_type_id' => $event->getParticipationTypeId(),
            'creator_user_id' => $event->getCreatorUserId(),
            'creator_team_id' => $event->getCreatorTeamId(),
        ];
    }
}
