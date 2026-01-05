<?php

namespace App\Domain\Repositories\Events;

interface IEventTypeRepository
{
    /**
     * @return array<array{id:string,name:string}>
     */
    public function findAll(): array;
}
