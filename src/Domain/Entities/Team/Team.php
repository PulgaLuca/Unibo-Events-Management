<?php

declare(strict_types=1);

namespace App\Domain\Entities\Team;

use App\Domain\Traits\HasAttributes;

class Team 
{
    use HasAttributes;

    public const STATUS_SEARCHING = 'Searching';
    public const STATUS_FULL = 'Full';
    public const STATUS_INACTIVE = 'Inactive';

    public function isFull(): bool 
    {
        return $this->status === self::STATUS_FULL;
    }

    public function isSearching(): bool 
    {
        return $this->status === self::STATUS_SEARCHING;
    }

    public function isInactive(): bool 
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    public function canAcceptMembers(): bool 
    {
        return $this->isSearching() && !$this->isFull();
    }
}
