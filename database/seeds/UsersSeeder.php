<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class UsersSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        $users = [
            [
                'id' => 1,
                'email' => 'alice@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'first_name' => 'Alice',
                'last_name' => 'Rossi',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 2,
                'email' => 'bob@example.com',
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'first_name' => 'Bob',
                'last_name' => 'Bianchi',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->table('users')->insert($users)->saveData();
    }
}
