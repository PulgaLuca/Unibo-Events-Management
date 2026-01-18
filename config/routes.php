<?php

declare(strict_types=1);

use App\Presentation\Controllers\Auth\UserController;
use App\Presentation\Controllers\Auth\ProfileController;
use App\Presentation\Controllers\Admin\AdminController;
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
    ['GET', '/profile/{id:\d+}', [ProfileController::class, 'viewUser']],

    // Admin routes
    ['GET', '/admin/stats', [AdminController::class, 'showStats']],
    ['GET', '/admin/skills', [AdminController::class, 'showSkills']],
    ['POST', '/admin/skills', [AdminController::class, 'createSkill']],
    ['POST', '/admin/skills/{id:\d+}', [AdminController::class, 'updateSkill']],
    ['POST', '/admin/skills/{id:\d+}/delete', [AdminController::class, 'deleteSkill']],
    ['GET', '/admin/users', [AdminController::class, 'showUsers']],
    ['POST', '/admin/users/{id:\d+}/toggle-admin', [AdminController::class, 'toggleAdmin']],

    ['GET', '/events', [EventController::class, 'showEventMainPage']],  // Visualizza tutti gli eventi
    ['GET', '/events/create', [EventController::class, 'showEventCreatePage']],  // Visualizza il modulo per creare un evento
    ['POST', '/events/create', [EventController::class, 'storeEvent']],  // Gestisce la creazione dell'evento
    ['GET', '/events/filter', [EventController::class, 'filterEvents']],
    ['GET', '/events/{id}', [EventController::class, 'showEventDetails']],  // Visualizza un singolo evento
    ['POST', '/events/{id}', [EventController::class, 'updateEvent']],  // Gestisce la modifica dell'evento
    ['POST', '/events/{id}/delete', [EventController::class, 'deleteEvent']],  // Gestisce la cancellazione dell'evento
    ['POST', '/events/{id}/subscribe', [EventController::class, 'subscribeToEvent']],  // Gestisce la cancellazione dell'evento
    ['POST', '/events/{id}/unsubscribe', [EventController::class, 'unsubscribeFromEvent']],  // Gestisce la cancellazione dell'evento

    ['GET', '/home', [EventController::class, 'home']]
];