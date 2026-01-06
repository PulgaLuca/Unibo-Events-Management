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

    ['GET', '/events', [EventController::class, 'showEventMainPage']],  // Visualizza tutti gli eventi
    ['GET', '/events/create', [EventController::class, 'showEventCreatePage']],  // Visualizza il modulo per creare un evento
    ['POST', '/events/create', [EventController::class, 'storeEvent']],  // Gestisce la creazione dell'evento
    ['GET', '/events/{id}', [EventController::class, 'showEventDetails']],  // Visualizza un singolo evento
    ['GET', '/events/{id}/edit', [EventController::class, 'showEventEditPage']],  // Visualizza il modulo di modifica per un evento
    ['POST', '/events/{id}', [EventController::class, 'updateEvent']],  // Gestisce la modifica dell'evento
    ['POST', '/events/{id}/delete', [EventController::class, 'deleteEvent']]  // Gestisce la cancellazione dell'evento
];