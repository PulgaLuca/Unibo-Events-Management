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
        $first_name = trim($data['first_name'] ?? '');
        $last_name = trim($data['last_name'] ?? '');
        $role = trim($data['role'] ?? '');

        try {
            $user = $this->authService->register($email, $password, $first_name, $last_name, $role);
            
            // Redirect to login page with success message
            $html = $this->twig->render('register.twig', [
                'success' => 'Registration successful! Please login.',
                'email' => $email,
            ]);
            return Response::html($html);
        } catch (Exception $e) {
            // Re-render form with error
            $html = $this->twig->render('register.twig', [
                'error' => $e->getMessage(),
                'email' => $email,
            ]);
            return Response::html($html, 400);
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
            return Response::redirect($_ENV['APP_URL']);
        } catch (Exception $e) {
            // Re-render form with error
            $html = $this->twig->render('login.twig', [
                'error' => $e->getMessage(),
                'email' => $email
            ]);
            return Response::html($html, 401);
        }
    }

    /**
     * Logout user
     */
    public function logout(): Response
    {
        $this->authService->logout();
        return Response::redirect($_ENV['APP_URL'] . '/login');
    }
}
