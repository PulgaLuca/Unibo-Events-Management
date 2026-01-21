<?php

namespace App\Domain\Repositories\Events;

interface IEventTypeRepository
{
    public function findAll(): array;
}
