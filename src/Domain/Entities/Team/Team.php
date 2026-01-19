<?php

declare(strict_types=1);

namespace App\Domain\Entities\Team;

use DateTime;

class Team {
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public string $status,
        public int $min,
        public ?int $max,
        public DateTime $createdAt
    ) {}
}
