<?php

declare(strict_types=1);

namespace App\Application\Services\Team;

use App\Domain\Entities\Team\Team;
use App\Domain\Repositories\Team\ITeamRepository;

class CreateTeam {
    
    private ITeamRepository $teamRepository;

    public function __construct(ITeamRepository $repo)
    {
        $this->teamRepository = $repo;
    }

    public function execute($data, $userId) {
        $team = new Team(
            uniqid("TM"),
            $data['name'],
            $data['description'],
            'Searching',
            $data['min'],
            $data['max'],
            $userId
        );
        $this->teamRepository->create($team);
        return $team;
    }
}
