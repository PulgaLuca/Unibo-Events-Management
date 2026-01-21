<?php

namespace App\Domain\Repositories\Events;

interface IParticipationTypeRepository
{
    public function findAll(): array;
}
