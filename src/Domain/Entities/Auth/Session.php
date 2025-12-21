<?php

declare(strict_types=1);

namespace App\Domain\Entities\Auth;

use App\Domain\Traits\HasAttributes;

class Session
{
    use HasAttributes;

    public function isExpired(): bool
    {
        $expiresAt = $this->expires_at;
        return $expiresAt && strtotime($expiresAt) < time();
    }
}
