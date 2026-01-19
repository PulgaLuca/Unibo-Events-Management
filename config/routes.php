<?php

declare(strict_types=1);

use App\Presentation\Controllers\Auth\UserController;
use App\Presentation\Controllers\Auth\ProfileController;
use App\Presentation\Controllers\Admin\AdminController;
use App\Presentation\Controllers\HomeController;
use App\Presentation\Controllers\Events\EventController;
use App\Presentation\Controllers\Team\TeamController;

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
    ['POST', '/events/{id}/delete', [EventController::class, 'deleteEvent']], // Gestisce la cancellazione dell'evento
    ['POST', '/events/{id}/subscribe', [EventController::class, 'subscribeToEvent']],  // Gestisce la cancellazione dell'evento
    ['POST', '/events/{id}/unsubscribe', [EventController::class, 'unsubscribeFromEvent']],  // Gestisce la cancellazione dell'evento
    
    ['GET', '/teams', [TeamController::class, 'index']],          // lista team
    ['GET', '/teams/my-teams', [TeamController::class, 'myTeams']], // i miei team
    ['GET', '/teams/create', [TeamController::class, 'create']], // form crea team
    ['POST', '/teams', [TeamController::class, 'store']],        // salva team
    ['GET', '/teams/{id}', [TeamController::class, 'show']],     // dettaglio team
    ['GET', '/teams/{id}/edit', [TeamController::class, 'edit']], // form modifica team
    ['POST', '/teams/{id}/update', [TeamController::class, 'update']], // aggiorna team
    ['POST', '/teams/{id}/join', [TeamController::class, 'join']], // richiesta entra nel team
    ['POST', '/teams/{id}/approve', [TeamController::class, 'approveMember']], // approva membro
    ['POST', '/teams/{id}/reject', [TeamController::class, 'rejectMember']], // rifiuta richiesta
    ['POST', '/teams/{id}/leave', [TeamController::class, 'leave']], // lascia team
    ['POST', '/teams/{id}/remove', [TeamController::class, 'removeMember']], // rimuovi membro
    ['POST', '/teams/{id}/promote', [TeamController::class, 'promoteMember']], // promuovi a leader
    ['POST', '/teams/{id}/status', [TeamController::class, 'changeStatus']], // cambia stato team

    ['GET', '/home', [EventController::class, 'home']]
];