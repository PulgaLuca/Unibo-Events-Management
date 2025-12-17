<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Application\Services\UserService;
use App\Infrastructure\Persistence\MySQLUserRepository;

session_start();

$service = new UserService(
    new MySQLUserRepository()
);

require 'user_test.html';
