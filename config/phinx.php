<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$root = dirname(__DIR__);
if (file_exists("{$root}/.env")) {
    Dotenv::createImmutable($root)->safeLoad();
}

return [
    'paths' => [
        'migrations' => "{$root}/database/migrations",
        'seeds' => "{$root}/database/seeds",
    ],
    'environments' => [
        'default_migration_table' => 'phinx_log',
        'default_environment' => 'development',
        'development' => [
            'adapter' => 'mysql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'name' => $_ENV['DB_NAME'] ?? 'unibo_matchskills_db',
            'user' => $_ENV['DB_USER'] ?? $_ENV['DB_USERNAME'] ?? 'root',
            'pass' => $_ENV['DB_PASSWORD'] ?? 'root',
            'port' => (int) ($_ENV['DB_PORT'] ?? 3306),
            'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
        ],
    ],
    'version_order' => 'creation',
];
