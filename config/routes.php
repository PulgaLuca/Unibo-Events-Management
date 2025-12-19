<?php

declare(strict_types=1);

use App\Presentation\Controllers\Auth\AuthController;
use App\Presentation\Controllers\HomeController;

return [
    ['GET', '/', [HomeController::class, 'index']],
    ['POST', '/register', [AuthController::class, 'register']],
    ['POST', '/login', [AuthController::class, 'login']],
    ['POST', '/logout', [AuthController::class, 'logout']],
    // Add other routes as needed
];
