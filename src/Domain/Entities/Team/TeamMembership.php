<?php

declare(strict_types=1);

namespace App\Domain\Entities\Team;

use App\Domain\Traits\HasAttributes;

class TeamMembership 
{
    use HasAttributes;

    public const STATUS_LEAD = 'Lead';
    public const STATUS_MEMBER = 'Member';
    public const STATUS_PENDING = 'Pending';

    public function isLeader(): bool 
    {
        return $this->status === self::STATUS_LEAD;
    }

    public function isMember(): bool 
    {
        return $this->status === self::STATUS_MEMBER;
    }

    public function isPending(): bool 
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isActive(): bool 
    {
        return $this->status === self::STATUS_LEAD || $this->status === self::STATUS_MEMBER;
    }
}
