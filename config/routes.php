<?php

declare(strict_types=1);

use App\Presentation\Controllers\Auth\UserController;
use App\Presentation\Controllers\Auth\ProfileController;
use App\Presentation\Controllers\HomeController;
use App\Presentation\Controllers\Events\EventController;
use App\Presentation\Controllers\Teams\TeamController;

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
    ['POST', '/events/{id}', [EventController::class, 'updateEvent']],  // Gestisce la modifica dell'evento
    ['POST', '/events/{id}/delete', [EventController::class, 'deleteEvent']], // Gestisce la cancellazione dell'evento
    ['POST', '/events/{id}/subscribe', [EventController::class, 'subscribeToEvent']],  // Gestisce la cancellazione dell'evento
    ['POST', '/events/{id}/unsubscribe', [EventController::class, 'unsubscribeFromEvent']],  // Gestisce la cancellazione dell'evento
    
    ['GET', '/teams', [TeamController::class, 'index']],          // lista team
    ['GET', '/teams/create', [TeamController::class, 'create']], // form crea team
    ['POST', '/teams', [TeamController::class, 'store']],        // salva team
    ['GET', '/teams/{id}', [TeamController::class, 'show']],     // dettaglio team
    ['POST', '/teams/{id}/join', [TeamController::class, 'join']] // entra nel team
];