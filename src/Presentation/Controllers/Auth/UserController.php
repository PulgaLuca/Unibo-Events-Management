<?php

declare(strict_types=1);

namespace App\Presentation\Controllers\Auth;

use App\Application\Services\Auth\AuthService;
use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;
use Exception;
use Twig\Environment;

class UserController
{
    private AuthService $authService;
    private Environment $twig;

    public function __construct(AuthService $authService, Environment $twig)
    {
        $this->authService = $authService;
        $this->twig = $twig;
    }

    /**
     * Show registration form
     */
    public function showRegister(): Response
    {
        $html = $this->twig->render('register.twig');
        return Response::html($html);
    }

    /**
     * Handle registration form submission
     */
    public function submitRegister(Request $request): Response
    {
        $data = $request->getParsedBody();
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $first_name = trim($data['first_name']) ?? null;
        $last_name = trim($data['last_name']) ?? null;
        $role = trim($data['role']) ?? null;

        try {
            $user = $this->authService->register($email, $password, 
                                    $first_name, $last_name, $role);
            return Response::json([
                'message' => 'User registered successfully',
                'user_id' => $user->id,
            ], 201);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Show login form
     */
    public function showLogin(): Response
    {
        $html = $this->twig->render('login.twig');
        return Response::html($html);
    }

    /**
     * Handle login form submission
     */
    public function submitLogin(Request $request): Response
    {
        $data = $request->getParsedBody();
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        try {
            $token = $this->authService->login($email, $password);
            return Response::json([
                'message' => 'Login successful',
                'token' => $token,
            ]);
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], 401);
        }
    }

    /**
     * Logout user
     */
    public function logout(): Response
    {
        $this->authService->logout();
        return Response::json(['message' => 'Logged out successfully']);
    }
}
