<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Traits\HasAttributes;

class User 
{
    use HasAttributes;

    public function isAdmin(): bool {
        return $this->role === 'admin';
    }
}