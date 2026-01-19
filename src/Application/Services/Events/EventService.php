<?php

declare(strict_types=1);

namespace App\Application\Services\Events;

use App\Domain\Entities\Events\Event;
use App\Domain\Entities\Auth\User;
use App\Domain\Entities\Events\EventStatus;
use App\Domain\Repositories\Auth\IUserRepository;
use App\Domain\Repositories\Events\IEventRepository;
use App\Domain\Repositories\Events\IEventTypeRepository;
use App\Domain\Repositories\Events\IParticipationTypeRepository;

use Exception;
use Ramsey\Uuid\Uuid;

class EventService
{
    private IEventRepository $eventRepository;
    private IEventTypeRepository $eventTypeRepository;
    private IParticipationTypeRepository $participationTypeRepository;
    private IUserRepository $userRepository;

    public function __construct(
        IEventRepository $eventRepository,
        IEventTypeRepository $eventTypeRepository,
        IParticipationTypeRepository $participationTypeRepository,
        IUserRepository $userRepository
    ) {
        $this->eventRepository = $eventRepository;
        $this->eventTypeRepository = $eventTypeRepository;
        $this->participationTypeRepository = $participationTypeRepository;
        $this->userRepository = $userRepository;
    }

    public function subscribeUser(string $eventId, int $userId): void
    {
        $this->eventRepository->createParticipation([
            'id' => Uuid::uuid4()->toString(),
            'event_id' => $eventId,
            'user_id' => $userId,
            'team_id' => null,
            'role' => 'Participant',
            'registration_date' => date("Y-m-d H:i:s")
        ]);
    }

    public function unsubscribeUser(string $eventId, int $userId): void
    {
        $this->eventRepository->deleteParticipation($eventId, $userId);
    }

    public function isUserSubscribed(string $eventId, int $userId): bool
    {
        return $this->eventRepository->checkParticipation($eventId, $userId);
    }

    public function resolveUserRoleInEvent(string $eventId, int $userId): ?string
    {
        return $this->eventRepository->getUserEventRole($eventId, $userId);
    }

    /**
     * Create a new Event
     */
    public function create(array $data, int $organizerId): Event
    {
        $this->validate($data);
        $eventId = Uuid::uuid4()->toString();

        $event = new Event(
            $eventId,
            $data['title'],
            $data['description'] ?? null,
            new \DateTime($data['start_date']),
            isset($data['end_date']) ? new \DateTime($data['end_date']) : null,
            isset($data['image_url']) ? $data['image_url'] : '/assets/images/events/event-main.jpg',
            $data['location'] ?? null,
            $data['url'] ?? null,
            isset($data['registration_deadline']) ? new \DateTime($data['registration_deadline']) : null,
            (int) ($data['min_participants'] ?? 0),
            isset($data['max_participants']) ? (int) $data['max_participants'] : null,
            EventStatus::fromString($data['status']),
            $data['type_id'],
            $data['participation_type_id'],
            $organizerId
        );
        
        $this->eventRepository->save($event);
        $this->subscribeUser($eventId, $organizerId);

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
            isset($data['image_url']) ? $data['image_url'] : $event->getImageUrl(),
            $data['location'] ?? $event->getLocation(),
            $data['url'] ?? $event->getUrl(),
            isset($data['registration_deadline']) ? new \DateTime($data['registration_deadline']) : $event->getRegistrationDeadline(),
            (int)($data['min_participants'] ?? $event->getMinParticipants()),
            array_key_exists('max_participants', $data) ? (int)$data['max_participants'] : (int)$event->getMaxParticipants(),
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

    public function getEventTypes(): array
    {
        return $this->eventTypeRepository->findAll();
    }

    public function getParticipationTypes(): array
    {
        return $this->participationTypeRepository->findAll();
    }

    public function enrichEventsForUser(array $events, int $userId): array
    {
        $result = [];

        foreach ($events as $event) {
            $eventId = $event->getEventId();

            $result[] = [
                'event' => $event,
                'isCreator' => $event->getCreatorUserId() === $userId,
                'isSubscribed' => $this->isUserSubscribed($eventId, $userId),
                'userRole' => $this->resolveUserRoleInEvent($eventId, $userId)
            ];
        }

        return $result;
    }

    public function getEventCreator(string $eventId): ?User
    {
        $event = $this->findById($eventId);

        if (!$event || !$event->getCreatorUserId()) {
            return null;
        }

        return $this->userRepository->findById(
            $event->getCreatorUserId()
        );
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
