<?php

declare(strict_types=1);

namespace App\Domain\Entities\Events;

use DateTime;
use InvalidArgumentException;

class Event
{
    private string $eventId;
    private string $title;
    private ?string $description;
    private DateTime $startDate;
    private ?DateTime $endDate;
    private ?string $imageUrl;
    private ?Location $location;
    private ?string $url;
    private ?DateTime $registrationDeadline;
    private int $minParticipants;
    private ?int $maxParticipants;
    private EventStatus $status;
    private string $typeId;
    private string $participationTypeId;
    private ?int $creatorUserId;
    private ?string $creatorTeamId;
    private array $requiredSkills = [];

    public function __construct(
        string $eventId,
        string $title,
        ?string $description,
        DateTime $startDate,
        ?DateTime $endDate,
        ?string $imageUrl,
        ?Location $location,
        ?string $url,
        ?DateTime $registrationDeadline,
        int $minParticipants,
        ?int $maxParticipants,
        EventStatus $status,
        string $typeId,
        string $participationTypeId,
        ?int $creatorUserId,
        array $requiredSkills = []
    ) {
        $this->assertValidDates($startDate, $endDate, $registrationDeadline);
        $this->assertValidParticipants($minParticipants, $maxParticipants);

        $this->eventId = $eventId;
        $this->title = $title;
        $this->description = $description;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->imageUrl = $imageUrl;
        $this->location = $location;
        $this->url = $url;
        $this->registrationDeadline = $registrationDeadline;
        $this->minParticipants = $minParticipants;
        $this->maxParticipants = $maxParticipants;
        $this->status = $status;
        $this->typeId = $typeId;
        $this->participationTypeId = $participationTypeId;
        $this->creatorUserId = $creatorUserId;
        $this->requiredSkills = $requiredSkills;
    }

    /**
     * Update mutable fields
     */
    public function update(
        string $title,
        ?string $description,
        DateTime $startDate,
        ?DateTime $endDate,
        ?string $imageUrl,
        ?Location $location,
        ?string $url,
        ?DateTime $registrationDeadline,
        int $minParticipants,
        ?int $maxParticipants,
        EventStatus $status
    ): void {
        $this->assertValidDates($startDate, $endDate, $registrationDeadline);
        $this->assertValidParticipants($minParticipants, $maxParticipants);

        $this->title = $title;
        $this->description = $description;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->imageUrl = $imageUrl;
        $this->location = $location;
        $this->url = $url;
        $this->registrationDeadline = $registrationDeadline;
        $this->minParticipants = $minParticipants;
        $this->maxParticipants = $maxParticipants;
        $this->status = $status;
    }

    /**
     * Domain invariants
     */
    private function assertValidDates(DateTime $startDate, ?DateTime $endDate, ?DateTime $registrationDeadline): void 
    {
        if ($endDate !== null && $endDate < $startDate) {
            throw new InvalidArgumentException('End date cannot be before start date');
        }

        if ($registrationDeadline !== null && $registrationDeadline > $startDate) {
            throw new InvalidArgumentException(
                'Registration deadline cannot be after event start date'
            );
        }
    }

    private function assertValidParticipants(int $minParticipants, ?int $maxParticipants): void 
    {
        if ($minParticipants < 0) {
            throw new InvalidArgumentException('Min participants cannot be negative');
        }

        if ($maxParticipants !== null && $minParticipants > $maxParticipants) {
            throw new InvalidArgumentException(
                'Min participants cannot exceed max participants'
            );
        }
    }

    // ----------------------------  Getters ---------------------------- 
    public function getEventId(): string
    {
        return $this->eventId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    public function getEndDate(): ?DateTime
    {
        return $this->endDate;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getRegistrationDeadline(): ?DateTime
    {
        return $this->registrationDeadline;
    }

    public function getMinParticipants(): int
    {
        return $this->minParticipants;
    }

    public function getMaxParticipants(): ?int
    {
        return $this->maxParticipants;
    }

    public function getStatus(): EventStatus
    {
        return $this->status;
    }

    public function getTypeId(): string
    {
        return $this->typeId;
    }

    public function getParticipationTypeId(): string
    {
        return $this->participationTypeId;
    }

    public function getCreatorUserId(): ?int
    {
        return $this->creatorUserId;
    }

    public function getRequiredSkills(): array
    {
        return $this->requiredSkills;
    }

    public function getRequiredSkillsAsString(): string
    {
        return implode(', ', array_column($this->requiredSkills, 'name'));
    }


    // public function getCreatorTeamId(): ?string
    // {
    //     return $this->creatorTeamId;
    // }
}
