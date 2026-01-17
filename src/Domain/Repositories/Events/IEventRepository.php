<?php

declare(strict_types=1);

namespace App\Domain\Repositories\Events;

use App\Domain\Entities\Events\Event;

interface IEventRepository
{
    /**
     * Persist a new Event
     */
    public function save(Event $event): void;

    /**
     * Update an existing Event
     */
    public function update(Event $event): void;

    /**
     * Remove Event by ID
     */
    public function delete(string $eventId): void;

    /**
     * Find Event by ID
     */
    public function findById(string $eventId): ?Event;

    /**
     * Retrieve all Events
     *
     * @return Event[]
     */
    public function findAll(): array;

    public function createParticipation(array $data): void;

    public function deleteParticipation(string $eventId, int $userId): void;

    public function checkParticipation(string $eventId, int $userId): bool;

    public function getUserEventRole(string $eventId, int $userId): ?string;

    public function getEventParticipants(string $eventId): array;
    
    public function getEventTeamsWithMembers(string $eventId): array;
    
    public function findEventsCreatedByUser(int $userId): array;
    
    public function findEventsJoinedByUser(int $userId): array;
    
    public function getRequiredSkills(string $eventId): array;
}
