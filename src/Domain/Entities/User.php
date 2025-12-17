<?php

namespace App\Domain\Entities;

class User 
{
    public function __construct(
        public string $id,
        public string $nome,
        public string $cognome,
        public string $email,
        public string $passwordHash,
        public bool $isAdmin = false,
        public bool $isProfessor = false,
        public bool $isMentor = false,
        public ?string $ultimoAccesso = null
    ) {}

    public function isStaff(): bool {
        return $this->isAdmin || $this->isProfessor || $this->isMentor;
    }
}
?>