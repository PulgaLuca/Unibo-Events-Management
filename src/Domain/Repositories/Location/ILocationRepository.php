<?php

declare(strict_types=1);

namespace App\Domain\Repositories\Location;

use App\Domain\Entities\Events\Location;

interface ILocationRepository
{
    public function findById(string $id): ?Location;

    public function create(Location $location): void;

    public function update(Location $location): void;
}
