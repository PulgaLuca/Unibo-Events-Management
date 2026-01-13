<?php
namespace Application\Team;

use Domain\Team\Team;
use Infrastructure\Team\TeamRepository;

class CreateTeam {
    public function __construct(private TeamRepository $repo) {}

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
        $this->repo->create($team);
        return $team;
    }
}
