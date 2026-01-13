<?php

declare(strict_types=1);

namespace App\Presentation\Controllers\Teams;

use App\Application\Teams\CreateTeamUseCase;
use App\Infrastructure\Teams\TeamRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TeamController
{
    private CreateTeamUseCase $createTeam;

    public function __construct(TeamRepository $teamRepository)
    {
        $this->createTeam = new CreateTeamUseCase($teamRepository);
    }

    public function store(Request $request, Response $response): Response
    {
        session_start();

        $data = $request->getParsedBody();
        $userId = $_SESSION['user_id'];

        $this->createTeam->execute(
            $data['name'],
            $data['description'],
            (int)$data['min_participants'],
            (int)$data['max_participants'],
            $userId
        );

        return $response
            ->withHeader('Location', '/teams')
            ->withStatus(302);
    }
}
