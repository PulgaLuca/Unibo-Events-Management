<?php

declare(strict_types=1);

namespace App\Domain\Repositories\Skill;

use App\Domain\Entities\Skill\Skill;

interface ISkillRepository
{
    public function findById(int $skillId): ?Skill;
    public function findByName(string $name): ?Skill;
    public function findAll(): array;
    public function findByCategory(string $category): array;
    public function create(Skill $skill): Skill;
    public function update(Skill $skill): bool;
    public function delete(int $skillId): bool;
    public function findOrCreateByName(string $name): int;
    public function getRequiredSkills(string $eventId): array;
}
