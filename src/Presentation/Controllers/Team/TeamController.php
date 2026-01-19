<?php

declare(strict_types=1);

namespace Presentation\Controllers\Team;

use Application\Services\Team\CreateTeam;
use Domain\Repositories\Team\TeamRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

final class TeamController
{
    private CreateTeam $createTeam;
    private TeamRepositoryInterface $teamRepository;
    private Environment $view;

    public function __construct(
        CreateTeam $createTeam,
        TeamRepositoryInterface $teamRepository,
        Environment $view
    ) {
        $this->createTeam = $createTeam;
        $this->teamRepository = $teamRepository;
        $this->view = $view;
    }

    /**
     * GET /teams
     * Mostra SOLO i team dell'utente loggato
     */
    public function index(Request $request, Response $response): Response
    {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            return $response
                ->withHeader('Location', '/login')
                ->withStatus(302);
        }

        $userId = (int) $_SESSION['user_id'];

        $teams = $this->teamRepository->findByUserId($userId);

        foreach ($teams as $team) {
            $team->memberCount = $this->teamRepository->countMembers($team->id);
        }

        return $this->view->render($response, 'teams/index.twig', [
            'teams' => $teams
        ]);
    }

    /**
     * GET /teams/create
     */
    public function create(Request $request, Response $response): Response
    {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            return $response
                ->withHeader('Location', '/login')
                ->withStatus(302);
        }

        return $this->view->render($response, 'teams/create.twig');
    }

    /**
     * POST /teams
     */
    public function store(Request $request, Response $response): Response
    {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            return $response
                ->withHeader('Location', '/login')
                ->withStatus(302);
        }

        $data = $request->getParsedBody();
        $userId = (int) $_SESSION['user_id'];

        try {
            $team = $this->createTeam->execute(
                name: $data['name'] ?? '',
                description: $data['description'] ?? '',
                minParticipants: (int) ($data['min_participants'] ?? 1),
                maxParticipants: !empty($data['max_participants'])
                    ? (int) $data['max_participants']
                    : null,
                mentorId: $userId
            );

            $_SESSION['success'] = 'Team creato con successo';

            return $response
                ->withHeader('Location', '/teams')
                ->withStatus(302);

        } catch (\InvalidArgumentException $e) {
            $_SESSION['error'] = $e->getMessage();
            $_SESSION['old_input'] = $data;

            return $response
                ->withHeader('Location', '/teams/create')
                ->withStatus(302);
        }
    }

    /**
     * POST /teams/{id}/join
     */
    public function join(Request $request, Response $response, array $args): Response
    {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            return $response
                ->withHeader('Location', '/login')
                ->withStatus(302);
        }

        $teamId = (int) ($args['id'] ?? 0);
        $userId = (int) $_SESSION['user_id'];

        try {
            $this->teamRepository->addMember($teamId, $userId);
            $_SESSION['success'] = 'Ti sei unito al team';

        } catch (\DomainException $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        return $response
            ->withHeader('Location', '/teams')
            ->withStatus(302);
    }
}
