<?php
interface UserRepositoryInterface {
    public function findByEmail(string $email): ?User;
    public function findById(string $id): ?User;
    public function save(User $user): void;
    public function updateLastLogin(string $id): void;
}
?>