<?php

declare(strict_types=1);

namespace App\Application\Services\Events;

use App\Domain\Entities\Events\Event;
use App\Domain\Entities\Events\EventStatus;
use App\Domain\Repositories\Events\IEventRepository;
use Exception;
use Ramsey\Uuid\Uuid;

class EventService
{
    private IEventRepository $eventRepository;

    public function __construct(IEventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * Create a new Event
     */
    public function create(array $data): Event
    {
        $this->validate($data);

        $event = new Event(
            Uuid::uuid4()->toString(),
            $data['title'],
            $data['description'] ?? null,
            new \DateTime($data['start_date']),
            isset($data['end_date']) ? new \DateTime($data['end_date']) : null,
            $data['location'] ?? null,
            $data['url'] ?? null,
            isset($data['registration_deadline']) ? new \DateTime($data['registration_deadline']) : null,
            (int) ($data['min_participants'] ?? 0),
            isset($data['max_participants']) ? (int) $data['max_participants'] : null,
            EventStatus::fromString($data['status']),
            $data['type_id'],
            $data['participation_type_id'],
            $data['creator_user_id'] ?? null,
            $data['creator_team_id'] ?? null
        );

        $this->eventRepository->save($event);

        return $event;
    }

    /**
     * Update existing Event
     */
    public function update(string $eventId, array $data): Event
    {
        $event = $this->eventRepository->findById($eventId);

        if (!$event) {
            throw new Exception('Event not found');
        }

        $event->update(
            $data['title'] ?? $event->getTitle(),
            $data['description'] ?? $event->getDescription(),
            isset($data['start_date']) ? new \DateTime($data['start_date']) : $event->getStartDate(),
            array_key_exists('end_date', $data) ? ($data['end_date'] ? new \DateTime($data['end_date']) : null) : $event->getEndDate(),
            $data['location'] ?? $event->getLocation(),
            $data['url'] ?? $event->getUrl(),
            isset($data['registration_deadline']) ? new \DateTime($data['registration_deadline']) : $event->getRegistrationDeadline(),
            $data['min_participants'] ?? $event->getMinParticipants(),
            array_key_exists('max_participants', $data) ? $data['max_participants'] : $event->getMaxParticipants(),
            isset($data['status']) ? EventStatus::fromString($data['status']) : $event->getStatus()
        );

        $this->eventRepository->update($event);

        return $event;
    }

    /**
     * Delete Event
     */
    public function delete(string $eventId): void
    {
        $event = $this->eventRepository->findById($eventId);

        if (!$event) {
            throw new Exception('Event not found');
        }

        $this->eventRepository->delete($eventId);
    }

    /**
     * Get Event by ID
     */
    public function findById(string $eventId): Event
    {
        $event = $this->eventRepository->findById($eventId);

        if (!$event) {
            throw new Exception('Event not found');
        }

        return $event;
    }

    /**
     * Get all Events
     */
    public function findAll(): array
    {
        return $this->eventRepository->findAll();
    }

    /**
     * Basic business validation
     */
    private function validate(array $data): void
    {
        if (empty($data['title'])) {
            throw new Exception('Title is required');
        }

        if (empty($data['start_date'])) {
            throw new Exception('Start date is required');
        }

        if (isset($data['end_date']) && $data['end_date'] !== null) {
            if (new \DateTime($data['end_date']) < new \DateTime($data['start_date'])) {
                throw new Exception('End date cannot be before start date');
            }
        }

        if (isset($data['min_participants'], $data['max_participants']) && $data['max_participants'] !== null && $data['min_participants'] > $data['max_participants']) {
            throw new Exception('Min participants cannot exceed max participants');
        }

        if (empty($data['status'])) {
            throw new Exception('Status is required');
        }

        if (empty($data['type_id']) || empty($data['participation_type_id'])) {
            throw new Exception('Event type and participation type are required');
        }
    }
}
