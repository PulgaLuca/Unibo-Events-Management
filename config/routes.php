<?php

declare(strict_types=1);

use App\Presentation\Controllers\Auth\UserController;
use App\Presentation\Controllers\Auth\ProfileController;
use App\Presentation\Controllers\HomeController;
use App\Presentation\Controllers\Events\EventController;

return [
    ['GET', '/', [HomeController::class, 'index']],
    ['GET', '/register', [UserController::class, 'showRegister']],
    ['POST', '/register', [UserController::class, 'submitRegister']],
    ['GET', '/login', [UserController::class, 'showLogin']],
    ['POST', '/login', [UserController::class, 'submitLogin']],
    [['GET','POST'], '/logout', [UserController::class, 'logout']],
    ['GET', '/profile', [ProfileController::class, 'show']],
    ['POST', '/profile', [ProfileController::class, 'updateSkills']],

    ['GET', '/events', [EventController::class, 'index']],
    ['GET', '/events/create', [EventController::class, 'createEvent']],
    ['POST', '/events', [EventController::class, 'storeEvent']],
    ['GET', '/events/{id}', [EventController::class, 'showEvent']],
    ['GET', '/events/{id}/edit', [EventController::class, 'editEvent']],
    ['POST', '/events/{id}', [EventController::class, 'updateEvent']],
    ['POST', '/events/{id}/delete', [EventController::class, 'deleteEvent']]
];
