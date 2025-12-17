<?php

namespace App\Application\Services;

use App\Domain\Repositories\UserRepositoryInterface;
use App\Domain\Entities\User;
use Exception;

class UserService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    
    public function register(string $nome, string $cognome, string $email, string $plainPassword): User 
    {
        if ($this->userRepository->findByEmail($email)) {
            throw new Exception('Email già registrata');
        }

        $user = new User(
            $this->generateUuid(),
            $nome,
            $cognome,
            $email,
            password_hash($plainPassword, PASSWORD_BCRYPT),
            false, // is_admin
            false, // is_professor
            false  // is_mentor
        );

        $this->userRepository->save($user);

        return $user;
    }

    public function login(string $email, string $plainPassword): User
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new Exception('Credenziali non valide');
        }

        if (!password_verify($plainPassword, $user->passwordHash)) {
            throw new Exception('Credenziali non valide');
        }

        $this->userRepository->updateLastLogin($user->id);

        return $user;
    }

    public function getUserById(string $userId): ?User
    {
        return $this->userRepository->findById($userId);
    }

    public function assignRole(User $user, string $role): User
    {
        switch ($role) {
            case 'admin':
                $user->isAdmin = true;
                break;
            case 'professor':
                $user->isProfessor = true;
                break;
            case 'mentor':
                $user->isMentor = true;
                break;
            default:
                throw new Exception('Ruolo non valido');
        }

        $this->userRepository->save($user);

        return $user;
    }

    /**
     * UUID v4 (senza librerie esterne)
     */
    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
