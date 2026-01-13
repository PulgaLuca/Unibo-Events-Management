<?php
namespace Domain\Team;

class Team {
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public string $status,
        public int $min,
        public ?int $max,
        public int $mentorId
    ) {}
}
