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
            'host' => getenv('DB_HOST') ?: 'localhost',
            'name' => getenv('DB_NAME') ?: 'unibo_matchskills_db',
            'user' => getenv('DB_USER') ?: getenv('DB_USERNAME') ?: 'root',
            'pass' => getenv('DB_PASSWORD') ?: 'root',
            'port' => (int) (getenv('DB_PORT') ?: 3306),
            'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
        ],
    ],
    'version_order' => 'creation',
];
