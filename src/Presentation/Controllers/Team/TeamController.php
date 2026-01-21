<?php

declare(strict_types=1);

namespace App\Presentation\Controllers\Team;

use App\Application\Services\Auth\AuthService;
use App\Application\Services\Team\TeamService;
use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;
use Exception;
use Twig\Environment;

class TeamController
{
    private TeamService $teamService;
    private AuthService $authService;
    private Environment $twig;

    public function __construct(TeamService $teamService, AuthService $authService, Environment $twig)
    {
        $this->teamService = $teamService;
        $this->authService = $authService;
        $this->twig = $twig;
    }

    /**
     * List all teams
     */
    public function index(Request $request): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::redirect('/login');
        }

        $currentUser = $this->authService->getCurrentUser();
        $queryParams = $request->getQueryParams();
        $showOnlySearching = isset($queryParams['searching']) && $queryParams['searching'];

        $teams = $showOnlySearching 
            ? $this->teamService->getSearchingTeams()
            : $this->teamService->getAllTeams();

        return new Response(
            $this->twig->render('team/index.twig', [
                'teams' => $teams,
                'currentUser' => $currentUser,
                'showOnlySearching' => $showOnlySearching
            ])
        );
    }

    /**
     * Show team details
     */
    public function show(string $id): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::redirect('/login');
        }

        $teamId = $id;
        $currentUser = $this->authService->getCurrentUser();
        
        $teamData = $this->teamService->getTeamWithMembers($teamId);
        
        if (!$teamData) {
            $_SESSION['error'] = 'Team not found';
            return Response::redirect('/teams');
        }

        return new Response(
            $this->twig->render('team/show.twig', [
                'team' => $teamData['team'],
                'members' => $teamData['members'],
                'pending_requests' => $teamData['pending_requests'],
                'member_count' => $teamData['member_count'],
                'currentUser' => $currentUser
            ])
        );
    }

    /**
     * Show create team form
     */
    public function create(Request $request): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::redirect('/login');
        }

        $currentUser = $this->authService->getCurrentUser();

        return new Response(
            $this->twig->render('team/create.twig', [
                'currentUser' => $currentUser
            ])
        );
    }

    /**
     * Store new team
     */
    public function store(Request $request): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::redirect('/login');
        }

        try {
            $currentUser = $this->authService->getCurrentUser();
            $data = $request->getParsedBody();

            $name = $data['name'] ?? '';
            $description = $data['description'] ?? null;
            $maxParticipants = (int) ($data['max_participants'] ?? 10);
            $minParticipants = (int) ($data['min_participants'] ?? 1);

            if (empty($name)) {
                throw new Exception("Team name is required");
            }

            $team = $this->teamService->createTeam(
                $currentUser->id,
                $name,
                $description,
                $maxParticipants,
                $minParticipants
            );

            $_SESSION['success'] = 'Team created successfully!';
            return Response::redirect("/teams/{$team->id}");
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            return Response::redirect('/teams/create');
        }
    }

    /**
     * Show edit team form
     */
    public function edit(string $id): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::redirect('/login');
        }

        $teamId = $id;
        $currentUser = $this->authService->getCurrentUser();
        
        $teamData = $this->teamService->getTeamWithMembers($teamId);
        
        if (!$teamData) {
            $_SESSION['error'] = 'Team not found';
            return Response::redirect('/teams');
        }

        return new Response(
            $this->twig->render('team/edit.twig', [
                'team' => $teamData['team'],
                'currentUser' => $currentUser
            ])
        );
    }

    /**
     * Update team
     */
    public function update(string $id, Request $request): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::redirect('/login');
        }

        try {
            $teamId = $id;
            $currentUser = $this->authService->getCurrentUser();
            $data = $request->getParsedBody();

            $name = $data['name'] ?? '';
            $description = $data['description'] ?? null;
            $maxParticipants = (int) ($data['max_participants'] ?? 10);
            $minParticipants = (int) ($data['min_participants'] ?? 1);

            if (empty($name)) {
                throw new Exception("Team name is required");
            }

            $this->teamService->updateTeam(
                $teamId,
                $currentUser->id,
                $name,
                $description,
                $maxParticipants,
                $minParticipants
            );

            $_SESSION['success'] = 'Team updated successfully!';
            return Response::redirect("/teams/{$teamId}");
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            return Response::redirect("/teams/{$teamId}/edit");
        }
    }

    /**
     * Request to join team
     */
    public function join(string $id, Request $request): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::json(['error' => 'Not authenticated'], 401);
        }

        try {
            $teamId = $id;
            $currentUser = $this->authService->getCurrentUser();

            $this->teamService->requestToJoin($teamId, $currentUser->id);

            if ($request->isXmlHttpRequest()) {
                return Response::json(['success' => true, 'message' => 'Join request sent']);
            }

            $_SESSION['success'] = 'Join request sent successfully!';
            return Response::redirect("/teams/{$teamId}");
        } catch (Exception $e) {
            if ($request->isXmlHttpRequest()) {
                return Response::json(['error' => $e->getMessage()], 400);
            }

            $_SESSION['error'] = $e->getMessage();
            return Response::redirect("/teams/{$teamId}");
        }
    }

    /**
     * Approve membership request
     */
    public function approveMember(string $id, Request $request): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::json(['error' => 'Not authenticated'], 401);
        }

        try {
            $teamId = $id;
            $data = $request->getParsedBody();
            $userId = (int) ($data['user_id'] ?? 0);
            $currentUser = $this->authService->getCurrentUser();

            $this->teamService->approveMembershipRequest($teamId, $currentUser->id, $userId);

            if ($request->isXmlHttpRequest()) {
                return Response::json(['success' => true, 'message' => 'Member approved']);
            }

            $_SESSION['success'] = 'Member approved successfully!';
            return Response::redirect("/teams/{$teamId}");
        } catch (Exception $e) {
            if ($request->isXmlHttpRequest()) {
                return Response::json(['error' => $e->getMessage()], 400);
            }

            $_SESSION['error'] = $e->getMessage();
            return Response::redirect("/teams/{$teamId}");
        }
    }

    /**
     * Reject membership request
     */
    public function rejectMember(string $id, Request $request): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::json(['error' => 'Not authenticated'], 401);
        }

        try {
            $teamId = $id;
            $data = $request->getParsedBody();
            $userId = (int) ($data['user_id'] ?? 0);
            $currentUser = $this->authService->getCurrentUser();

            $this->teamService->rejectMembershipRequest($teamId, $currentUser->id, $userId);

            if ($request->isXmlHttpRequest()) {
                return Response::json(['success' => true, 'message' => 'Request rejected']);
            }

            $_SESSION['success'] = 'Request rejected successfully!';
            return Response::redirect("/teams/{$teamId}");
        } catch (Exception $e) {
            if ($request->isXmlHttpRequest()) {
                return Response::json(['error' => $e->getMessage()], 400);
            }

            $_SESSION['error'] = $e->getMessage();
            return Response::redirect("/teams/{$teamId}");
        }
    }

    /**
     * Leave team
     */
    public function leave(string $id, Request $request): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::json(['error' => 'Not authenticated'], 401);
        }

        try {
            $teamId = $id;
            $currentUser = $this->authService->getCurrentUser();

            $this->teamService->leaveTeam($teamId, $currentUser->id);

            if ($request->isXmlHttpRequest()) {
                return Response::json(['success' => true, 'message' => 'Left team']);
            }

            $_SESSION['success'] = 'You have left the team';
            return Response::redirect('/teams');
        } catch (Exception $e) {
            if ($request->isXmlHttpRequest()) {
                return Response::json(['error' => $e->getMessage()], 400);
            }

            $_SESSION['error'] = $e->getMessage();
            return Response::redirect("/teams/{$teamId}");
        }
    }

    /**
     * Remove member from team
     */
    public function removeMember(string $id, Request $request): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::json(['error' => 'Not authenticated'], 401);
        }

        try {
            $teamId = $id;
            $data = $request->getParsedBody();
            $userId = (int) ($data['user_id'] ?? 0);
            $currentUser = $this->authService->getCurrentUser();

            $this->teamService->removeMember($teamId, $currentUser->id, $userId);

            if ($request->isXmlHttpRequest()) {
                return Response::json(['success' => true, 'message' => 'Member removed']);
            }

            $_SESSION['success'] = 'Member removed successfully!';
            return Response::redirect("/teams/{$teamId}");
        } catch (Exception $e) {
            if ($request->isXmlHttpRequest()) {
                return Response::json(['error' => $e->getMessage()], 400);
            }

            $_SESSION['error'] = $e->getMessage();
            return Response::redirect("/teams/{$teamId}");
        }
    }

    /**
     * Promote member to leader
     */
    public function promoteMember(string $id, Request $request): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::json(['error' => 'Not authenticated'], 401);
        }

        try {
            $teamId = $id;
            $data = $request->getParsedBody();
            $userId = (int) ($data['user_id'] ?? 0);
            $currentUser = $this->authService->getCurrentUser();

            $this->teamService->promoteMemberToLeader($teamId, $currentUser->id, $userId);

            if ($request->isXmlHttpRequest()) {
                return Response::json(['success' => true, 'message' => 'Member promoted to leader']);
            }

            $_SESSION['success'] = 'Member promoted to leader!';
            return Response::redirect("/teams/{$teamId}");
        } catch (Exception $e) {
            if ($request->isXmlHttpRequest()) {
                return Response::json(['error' => $e->getMessage()], 400);
            }

            $_SESSION['error'] = $e->getMessage();
            return Response::redirect("/teams/{$teamId}");
        }
    }

    /**
     * Get user's teams
     */
    public function myTeams(Request $request): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::redirect('/login');
        }

        $currentUser = $this->authService->getCurrentUser();
        $teams = $this->teamService->getUserTeams($currentUser->id);

        return new Response(
            $this->twig->render('team/my-teams.twig', [
                'teams' => $teams,
                'currentUser' => $currentUser
            ])
        );
    }

    /**
     * Change team status
     */
    public function changeStatus(string $id, Request $request): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::json(['error' => 'Not authenticated'], 401);
        }

        try {
            $teamId = $id;
            $data = $request->getParsedBody();
            $status = $data['status'] ?? '';
            $currentUser = $this->authService->getCurrentUser();

            $this->teamService->changeTeamStatus($teamId, $currentUser->id, $status);

            if ($request->isXmlHttpRequest()) {
                return Response::json(['success' => true, 'message' => 'Team status updated']);
            }

            $_SESSION['success'] = 'Team status updated!';
            return Response::redirect("/teams/{$teamId}");
        } catch (Exception $e) {
            if ($request->isXmlHttpRequest()) {
                return Response::json(['error' => $e->getMessage()], 400);
            }

            $_SESSION['error'] = $e->getMessage();
            return Response::redirect("/teams/{$teamId}");
        }
    }
}
