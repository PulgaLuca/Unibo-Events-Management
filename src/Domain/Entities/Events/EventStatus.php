<?php

declare(strict_types=1);

namespace App\Domain\Entities\Events;

enum EventStatus: string
{
    case Active = 'Active';
    case Completed = 'Completed';
    case Cancelled = 'Cancelled';
    case Draft = 'Draft';

    public static function fromString(string $status): self
    {
        return match ($status) {
            'Active' => self::Active,
            'Completed' => self::Completed,
            'Cancelled' => self::Cancelled,
            'Draft' => self::Draft,
            default => throw new \InvalidArgumentException("Invalid event status: $status"),
        };
    }
}
