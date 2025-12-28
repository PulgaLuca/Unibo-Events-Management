<?php

declare(strict_types=1);

namespace App\Presentation\Controllers\Auth;

use App\Application\Services\Auth\AuthService;
use App\Domain\Repositories\Auth\IUserRepository;
use App\Domain\Repositories\Skill\ISkillRepository;
use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;
use Exception;
use Twig\Environment;

class ProfileController
{
    private AuthService $authService;
    private IUserRepository $userRepository;
    private ISkillRepository $skillRepository;
    private Environment $twig;

    public function __construct(
        AuthService $authService,
        IUserRepository $userRepository,
        ISkillRepository $skillRepository,
        Environment $twig
    ) {
        $this->authService = $authService;
        $this->userRepository = $userRepository;
        $this->skillRepository = $skillRepository;
        $this->twig = $twig;
    }

    /**
     * Show user profile with skills management
     */
    public function show(): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::redirect($_ENV['APP_URL'] . '/login');
        }

        $user = $this->authService->getCurrentUser();
        $userSkills = $this->userRepository->getUserSkills($user->id);
        $allSkills = $this->skillRepository->findAll();
        
        // Group skills by category
        $skillsByCategory = [];
        foreach ($allSkills as $skill) {
            $skillsByCategory[$skill->category][] = $skill;
        }
        
        // Create a map of user's current skills for easy lookup
        $userSkillsMap = [];
        foreach ($userSkills as $userSkill) {
            $userSkillsMap[$userSkill['skill_id']] = $userSkill['level'];
        }

        $html = $this->twig->render('profile.twig', [
            'title' => 'My Profile',
            'userSkills' => $userSkills,
            'userSkillsMap' => $userSkillsMap,
            'skillsByCategory' => $skillsByCategory,
            'success' => $_GET['success'] ?? null,
            'error' => $_GET['error'] ?? null,
        ]);

        return Response::html($html);
    }

    /**
     * Update user skills
     */
    public function updateSkills(Request $request): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::redirect($_ENV['APP_URL'] . '/login');
        }

        $user = $this->authService->getCurrentUser();
        $data = $request->getParsedBody();
        
        try {
            // Get skills from form data
            // Format: skills[skill_id] = level
            $skills = [];
            if (isset($data['skills']) && is_array($data['skills'])) {
                foreach ($data['skills'] as $skillId => $level) {
                    $level = (int) $level;
                    if ($level > 0 && $level <= 4) {
                        $skills[(int) $skillId] = $level;
                    }
                }
            }
            
            $this->userRepository->setUserSkills($user->id, $skills);
            return Response::redirect($_ENV['APP_URL'] . '/profile?success=1');
        } catch (Exception $e) {
            return Response::redirect($_ENV['APP_URL'] . '/profile?error=1');
        }
    }
}
