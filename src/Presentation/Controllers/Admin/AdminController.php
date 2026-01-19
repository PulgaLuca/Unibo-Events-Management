<?php

declare(strict_types=1);

namespace App\Presentation\Controllers\Admin;

use App\Application\Services\Auth\AuthService;
use App\Domain\Repositories\Auth\IUserRepository;
use App\Domain\Repositories\Auth\ISessionRepository;
use App\Domain\Repositories\Skill\ISkillRepository;
use App\Domain\Entities\Skill\Skill;
use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;
use Exception;
use Twig\Environment;

class AdminController
{
    private AuthService $authService;
    private IUserRepository $userRepository;
    private ISessionRepository $sessionRepository;
    private ISkillRepository $skillRepository;
    private Environment $twig;

    public function __construct(
        AuthService $authService,
        IUserRepository $userRepository,
        ISessionRepository $sessionRepository,
        ISkillRepository $skillRepository,
        Environment $twig
    ) {
        $this->authService = $authService;
        $this->userRepository = $userRepository;
        $this->sessionRepository = $sessionRepository;
        $this->skillRepository = $skillRepository;
        $this->twig = $twig;
    }

    private function checkAdminAccess(): ?Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::redirect($_ENV['APP_URL'] . '/login');
        }

        $user = $this->authService->getCurrentUser();
        if (!$user->isAdmin()) {
            return Response::redirect($_ENV['APP_URL'] . '/?error=' . urlencode('Access denied'));
        }

        return null;
    }

    /**
     * Admin dashboard with statistics
     */
    public function showStats(): Response
    {
        if ($redirect = $this->checkAdminAccess()) {
            return $redirect;
        }

        $stats = $this->userRepository->getStatistics();

        $html = $this->twig->render('admin/stats.twig', [
            'title' => 'Admin Dashboard',
            'stats' => $stats,
        ]);

        return Response::html($html);
    }

    /**
     * Manage skills
     */
    public function showSkills(): Response
    {
        if ($redirect = $this->checkAdminAccess()) {
            return $redirect;
        }

        $skills = $this->skillRepository->findAll();
        
        // Group by category
        $skillsByCategory = [];
        foreach ($skills as $skill) {
            $skillsByCategory[$skill->category][] = $skill;
        }

        $html = $this->twig->render('admin/skills.twig', [
            'title' => 'Manage Skills',
            'skillsByCategory' => $skillsByCategory,
            'success' => $_GET['success'] ?? null,
            'error' => $_GET['error'] ?? null,
        ]);

        return Response::html($html);
    }

    /**
     * Add new skill
     */
    public function createSkill(Request $request): Response
    {
        if ($redirect = $this->checkAdminAccess()) {
            return $redirect;
        }

        $data = $request->getParsedBody();
        
        try {
            $skill = new Skill();
            $skill->name = trim($data['name'] ?? '');
            $skill->category = trim($data['category'] ?? '');

            if (empty($skill->name) || empty($skill->category)) {
                throw new Exception('Name and category are required');
            }

            $this->skillRepository->create($skill);
            return Response::redirect($_ENV['APP_URL'] . '/admin/skills?success=Skill added successfully');
        } catch (Exception $e) {
            return Response::redirect($_ENV['APP_URL'] . '/admin/skills?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Update skill
     */
    public function updateSkill(string $id, Request $request): Response
    {
        if ($redirect = $this->checkAdminAccess()) {
            return $redirect;
        }

        $data = $request->getParsedBody();
        $skillId = (int) $id;
        
        try {
            $skill = $this->skillRepository->findById($skillId);
            if (!$skill) {
                throw new Exception('Skill not found');
            }

            $skill->name = trim($data['name'] ?? '');
            $skill->category = trim($data['category'] ?? '');

            if (empty($skill->name) || empty($skill->category)) {
                throw new Exception('Name and category are required');
            }

            $this->skillRepository->update($skill);
            return Response::redirect($_ENV['APP_URL'] . '/admin/skills?success=Skill updated successfully');
        } catch (Exception $e) {
            return Response::redirect($_ENV['APP_URL'] . '/admin/skills?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Delete skill
     */
    public function deleteSkill(string $id): Response
    {
        if ($redirect = $this->checkAdminAccess()) {
            return $redirect;
        }

        $skillId = (int) $id;
        
        try {
            $this->skillRepository->delete($skillId);
            return Response::redirect($_ENV['APP_URL'] . '/admin/skills?success=Skill deleted successfully');
        } catch (Exception $e) {
            return Response::redirect($_ENV['APP_URL'] . '/admin/skills?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Manage users
     */
    public function showUsers(): Response
    {
        if ($redirect = $this->checkAdminAccess()) {
            return $redirect;
        }

        $users = $this->userRepository->getAllWithLastSession();

        $html = $this->twig->render('admin/users.twig', [
            'title' => 'Manage Users',
            'users' => $users,
            'success' => $_GET['success'] ?? null,
            'error' => $_GET['error'] ?? null,
        ]);

        return Response::html($html);
    }

    /**
     * Toggle user admin status
     */
    public function toggleAdmin(string $id): Response
    {
        if ($redirect = $this->checkAdminAccess()) {
            return $redirect;
        }

        $userId = (int) $id;
        
        try {
            $user = $this->userRepository->findById($userId);
            if (!$user) {
                throw new Exception('User not found');
            }

            // Toggle role
            $user->role = $user->role === 'admin' ? 'user' : 'admin';
            $this->userRepository->update($user);

            return Response::redirect($_ENV['APP_URL'] . '/admin/users?success=User role updated successfully');
        } catch (Exception $e) {
            return Response::redirect($_ENV['APP_URL'] . '/admin/users?error=' . urlencode($e->getMessage()));
        }
    }
}
