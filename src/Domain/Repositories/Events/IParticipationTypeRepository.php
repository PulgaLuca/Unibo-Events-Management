<?php

namespace App\Domain\Repositories\Events;

interface IParticipationTypeRepository
{
    /**
     * @return array<array{id:string,name:string}>
     */
    public function findAll(): array;
}
