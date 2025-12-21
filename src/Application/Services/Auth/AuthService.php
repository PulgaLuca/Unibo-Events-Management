<?php

declare(strict_types=1);

namespace App\Application\Services\Auth;

use App\Domain\Entities\Auth\User;
use App\Domain\Entities\Auth\Session;
use App\Domain\Repositories\Auth\IUserRepository;
use App\Domain\Repositories\Auth\ISessionRepository;
use Exception;

class AuthService
{
    private IUserRepository $userRepository;
    private ISessionRepository $sessionRepository;
    private int $sessionDurationHours = 24;

    public function __construct(IUserRepository $userRepository, ISessionRepository $sessionRepository)
    {
        $this->userRepository = $userRepository;
        $this->sessionRepository = $sessionRepository;
    }

    /**
     * Register a new user.
     * 
     */
    public function register(string $email, string $password, ?string $first_name = null, ?string $last_name = null,
                                ?string $role = null): User
    {
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address');
        }

        // Validate password length
        if (strlen($password) < 8) {
            throw new Exception('Password must be at least 8 characters');
        }

        // Check if user already exists
        if ($this->userRepository->existsByEmail($email)) {
            throw new Exception('User already exists');
        }

        // Create new user
        $user = new User();
        $user->email = $email;
        $user->password = password_hash($password, PASSWORD_BCRYPT);
        $user->first_name = $first_name;
        $user->last_name = $last_name;
        $user->role = $role;
        return $this->userRepository->create($user);
    }

    /**
     * Authenticate user by email and password.
     * Creates a session token and stores it in the database.
     * Returns the session token for client-side storage.
     * 
     */
    public function login(string $email, string $password): string
    {
        // Validate input
        if (!$email || !$password) {
            throw new Exception('Email and password are required');
        }

        // Find user by email
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            throw new Exception('Invalid credentials');
        }

        // Verify password
        if (!password_verify($password, $user->password)) {
            throw new Exception('Invalid credentials');
        }

        // Generate session token
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        // Create session record
        $session = new Session();
        $session->user_id = $user->id;
        $session->token_hash = $tokenHash;
        $session->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $session->expires_at = date('Y-m-d H:i:s', strtotime("+{$this->sessionDurationHours} hours"));
        $this->sessionRepository->create($session);

        // Set cookie with token (httponly for security)
        setcookie('session_token', $token, [
            'expires' => strtotime("+{$this->sessionDurationHours} hours"),
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict',
        ]);

        // Also store in session superglobal for server-side access
        $_SESSION['session_token'] = $token;

        return $token;
    }

    /**
     * Logout the current user.
     */
    public function logout(): void
    {
        $token = $_COOKIE['session_token'] ?? $_SESSION['session_token'] ?? null;

        if ($token) {
            $tokenHash = hash('sha256', $token);
            $this->sessionRepository->deleteByToken($tokenHash);
        }

        unset($_SESSION['session_token']);
        setcookie('session_token', '', ['expires' => time() - 3600, 'path' => '/']);
        session_destroy();
    }

    /**
     * Get the currently authenticated user by session token.
     */
    public function getCurrentUser(): ?User
    {
        $token = $_COOKIE['session_token'] ?? $_SESSION['session_token'] ?? null;

        if (!$token) {
            return null;
        }

        $tokenHash = hash('sha256', $token);
        $session = $this->sessionRepository->findByToken($tokenHash);

        if (!$session || $session->isExpired()) {
            if ($session) {
                $this->sessionRepository->deleteByToken($tokenHash);
            }
            return null;
        }

        return $this->userRepository->findById((int) $session->user_id);
    }

    /**
     * Check if a user is authenticated.
     */
    public function isAuthenticated(): bool
    {
        return $this->getCurrentUser() !== null;
    }
}
