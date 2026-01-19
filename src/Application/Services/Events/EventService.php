<?php

declare(strict_types=1);

namespace App\Application\Services\Events;

use App\Domain\Entities\Events\Event;
use App\Domain\Entities\Events\Location;
use App\Domain\Entities\Auth\User;
use App\Domain\Entities\Events\EventStatus;
use App\Domain\Repositories\Auth\IUserRepository;
use App\Domain\Repositories\Events\IEventRepository;
use App\Domain\Repositories\Events\IEventTypeRepository;
use App\Domain\Repositories\Location\ILocationRepository;
use App\Domain\Repositories\Events\IParticipationTypeRepository;
use App\Domain\Repositories\Skill\ISkillRepository;
use Exception;
use Ramsey\Uuid\Uuid;

class EventService
{
    private IEventRepository $eventRepository;
    private ILocationRepository $locationRepository;
    private IEventTypeRepository $eventTypeRepository;
    private IParticipationTypeRepository $participationTypeRepository;
    private IUserRepository $userRepository;
    private ISkillRepository $skillRepository;

    public function __construct(
        IEventRepository $eventRepository,
        ILocationRepository $locationRepository,
        IEventTypeRepository $eventTypeRepository,
        IParticipationTypeRepository $participationTypeRepository,
        IUserRepository $userRepository,
        ISkillRepository $skillRepository
    ) {
        $this->eventRepository = $eventRepository;
        $this->locationRepository = $locationRepository;
        $this->eventTypeRepository = $eventTypeRepository;
        $this->participationTypeRepository = $participationTypeRepository;
        $this->userRepository = $userRepository;
        $this->skillRepository = $skillRepository;
    }

    public function subscribeUser(string $eventId, int $userId, string $role): void
    {
        $this->eventRepository->createParticipation([
            'id' => Uuid::uuid4()->toString(),
            'event_id' => $eventId,
            'user_id' => $userId,
            'team_id' => null,
            'role' => $role,
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

        $location = null;

        if (!empty($data['location_country']) && !empty($data['location_city'])) {
            $location = new Location(
                Uuid::uuid4()->toString(),
                $data['location_country'],
                $data['location_city'],
                $data['location_description'] ?? null
            );

            $this->locationRepository->create($location);
        }

        $event = new Event(
            $eventId,
            $data['title'],
            $data['description'] ?? null,
            new \DateTime($data['start_date']),
            isset($data['end_date']) ? new \DateTime($data['end_date']) : null,
            isset($data['image_url']) ? $data['image_url'] : '/assets/images/events/event-main.jpg',
            $location,
            $data['url'] ?? null,
            isset($data['registration_deadline']) ? new \DateTime($data['registration_deadline']) : null,
            isset($data['min_participants']) ? (int) $data['min_participants'] : null,
            isset($data['max_participants']) ? (int) $data['max_participants'] : null,
            EventStatus::fromString($data['status']),
            $data['type_id'],
            $data['participation_type_id'],
            $organizerId
        );

        if (!empty($data['skills'])) {
            $this->attachRequiredSkills($eventId, $data['skills']);
        }

        $this->eventRepository->save($event);
        $this->subscribeUser($eventId, $organizerId, 'Lead');

        return $event;
    }

    private function attachRequiredSkills(string $eventId, string $skillsInput): void
    {
        $skills = array_filter(array_map(
            fn($s) => trim($s),
            explode(',', $skillsInput)
        ));

        foreach ($skills as $skillName) {
            $skill = $this->skillRepository->findOrCreateByName($skillName);
            $this->eventRepository->attachSkill($eventId, $skill);
        }
    }

    public function getRequiredSkills(string $eventId): array
    {
        return $this->eventRepository->getSkillsForEvent($eventId);
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

        $location = null;

        if (!empty($data['location_id'])) {
            $location = new Location(
                $data['location_id'],
                $data['location_country'],
                $data['location_city'],
                $data['location_description'] ?? null
            );

            $this->locationRepository->update($location);
        } else {
            $location = $event->getLocation();
        }

        $event->update(
            $data['title'] ?? $event->getTitle(),
            $data['description'] ?? $event->getDescription(),
            isset($data['start_date']) ? new \DateTime($data['start_date']) : $event->getStartDate(),
            array_key_exists('end_date', $data) ? ($data['end_date'] ? new \DateTime($data['end_date']) : null) : $event->getEndDate(),
            isset($data['image_url']) ? $data['image_url'] : $event->getImageUrl(),
            $location,
            $data['url'] ?? $event->getUrl(),
            isset($data['registration_deadline']) ? new \DateTime($data['registration_deadline']) : $event->getRegistrationDeadline(),
            (int)($data['min_participants'] ?? $event->getMinParticipants()),
            array_key_exists('max_participants', $data) ? (int)$data['max_participants'] : (int)$event->getMaxParticipants(),
            isset($data['status']) ? EventStatus::fromString($data['status']) : $event->getStatus()
        );

        $this->eventRepository->update($event);

        if (array_key_exists('skills', $data)) {
            $this->syncRequiredSkills($eventId, $data['skills']);
        }

        return $event;
    }

    private function syncRequiredSkills(string $eventId, string $skillsInput): void
    {
        // Normalizza input
        $skills = array_unique(array_filter(array_map(
            fn($s) => trim($s),
            explode(',', $skillsInput)
        )));

        // Skill attuali
        $currentSkillIds = $this->eventRepository->getSkillIdsForEvent($eventId);
        // Skill nuove
        $newSkillIds = [];

        foreach ($skills as $skillName) {
            $newSkillIds[] = $this->skillRepository->findOrCreateByName($skillName);
        }

        $toAdd = array_diff($newSkillIds, $currentSkillIds);
        $toRemove = array_diff($currentSkillIds, $newSkillIds);

        foreach ($toAdd as $skillId) {
            $this->eventRepository->attachSkill($eventId, $skillId);
        }

        foreach ($toRemove as $skillId) {
            $this->eventRepository->detachSkill($eventId, $skillId);
        }
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

    public function getEventsByFilters(array $filters, User $user): array
    {
        $preset = $filters['preset'] ?? null;

        if ($preset) {
            return match ($preset) {
                'my_upcoming' => $this->eventRepository->findMyUpcomingEvents($user->id),
                'hosted'      => $this->eventRepository->findHostedByUser($user->id),
                'trending'    => $this->eventRepository->findTrendingEvents(),
                'upcoming'    => $this->eventRepository->findUpcomingEvents(),
                'past'        => $this->eventRepository->findPastEvents(),
                default       => $this->eventRepository->findByFilters($filters),
            };
        }

        // No preset filters but only custom filters
        return $this->eventRepository->findByFilters($filters);
    }

    public function getEventParticipants(string $eventId): array
    {
        return $this->eventRepository->getEventParticipants($eventId);
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
