<?php

declare(strict_types=1);

use App\Presentation\Controllers\Auth\UserController;
use App\Presentation\Controllers\Auth\ProfileController;
use App\Presentation\Controllers\HomeController;

return [
    ['GET', '/', [HomeController::class, 'index']],
    ['GET', '/register', [UserController::class, 'showRegister']],
    ['POST', '/register', [UserController::class, 'submitRegister']],
    ['GET', '/login', [UserController::class, 'showLogin']],
    ['POST', '/login', [UserController::class, 'submitLogin']],
    [['GET','POST'], '/logout', [UserController::class, 'logout']],
    ['GET', '/profile', [ProfileController::class, 'show']],
    ['POST', '/profile', [ProfileController::class, 'updateSkills']],
];
