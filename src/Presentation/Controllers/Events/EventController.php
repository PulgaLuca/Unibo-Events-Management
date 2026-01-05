<?php

declare(strict_types=1);

namespace App\Presentation\Controllers\Events;

use App\Application\Services\Auth\AuthService;
use App\Application\Services\Events\EventService;
use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;
use Exception;
use Twig\Environment;

class EventController
{
    private EventService $eventService;
    private AuthService $authService;
    private Environment $twig;

    public function __construct(EventService $eventService, AuthService $authService, Environment $twig)
    {
        $this->eventService = $eventService;
        $this->authService = $authService;
        $this->twig = $twig;
    }

    /**
     * List all events
     */
    public function index(): Response
    {
        // TODO: to activate when login works correctly
        // if (!$this->authService->isAuthenticated()) {
        //     return Response::redirect($_ENV['APP_URL'] . '/login');
        // }

        $currentUser = $this->authService->getCurrentUser();
        $events = $this->eventService->findAll();
        
        $html = $this->twig->render('events/index.twig', [
            'events' => $events,
            'success' => $_SESSION['success'] ?? null,
            'currentUser' => $currentUser
        ]);

        // Rimuovi il messaggio di successo dalla sessione dopo averlo passato alla vista
        $_SESSION['success'] = null;

        return Response::html($html);
    }

    /**
     * Show create event form
     */
    public function createEvent(): Response
    {
        // TODO: to activate when login works correctly
        // if (!$this->authService->isAuthenticated()) {
        //     return Response::redirect($_ENV['APP_URL'] . '/login');
        // }

        $currentUser = $this->authService->getCurrentUser();

        $html = $this->twig->render('events/create.twig', [
            'eventTypes' => $this->eventService->getEventTypes(),
            'participationTypes' => $this->eventService->getParticipationTypes(),
            'currentUser' => $currentUser
        ]);

        return Response::html($html);
    }


    /**
     * Handle create event submission
     */
    public function storeEvent(Request $request): Response
    {
        // TODO: to activate when login works correctly
        // if (!$this->authService->isAuthenticated()) {
        //     return Response::redirect($_ENV['APP_URL'] . '/login');
        // }

        $currentUser = $this->authService->getCurrentUser();

        $data = $request->getParsedBody();

        try {
            $this->eventService->create($data);

            $html = $this->twig->render('events/create.twig', [
                'success' => 'Event created successfully!',
                'data'  => $data,
                'currentUser' => $currentUser
            ]);
            return Response::html($html);

        } catch (Exception $e) {
            $html = $this->twig->render('events/create.twig', [
                'error' => $e->getMessage(),
                'data'  => $data,
                'currentUser' => $currentUser
            ]);

            return Response::html($html, 400);
        }
    }

    /**
     * Show single event
     */
    public function showEvent(string $id): Response
    {
        try 
        {
            // TODO: to activate when login works correctly
            // if (!$this->authService->isAuthenticated()) {
            //     return Response::redirect($_ENV['APP_URL'] . '/login');
            // }

            $event = $this->eventService->findById($id);
            $currentUser = $this->authService->getCurrentUser();

            $html = $this->twig->render('events/show.twig', [
                'event' => $event,
                'eventTypes' => $this->eventService->getEventTypes(),
                'participationTypes' => $this->eventService->getParticipationTypes(),
                'currentUser' => $currentUser
            ]);

            return Response::html($html);
        } catch (Exception $e) {
            return Response::html('Event not found', 404);
        }
    }

    /**
     * Show edit event form
     */
    public function editEvent(string $id): Response
    {
        // TODO: to activate when login works correctly
        // if (!$this->authService->isAuthenticated()) {
        //     return Response::redirect($_ENV['APP_URL'] . '/login');
        // }

        $event = $this->eventService->findById($id);
        $currentUser = $this->authService->getCurrentUser();

        $html = $this->twig->render('events/edit.twig', [
            'event' => $event,
            'eventTypes' => $this->eventService->getEventTypes(),
            'participationTypes' => $this->eventService->getParticipationTypes(),
            'currentUser' => $currentUser
        ]);

        return Response::html($html);
    }

    /**
     * Handle update event submission
     */
    public function updateEvent(Request $request, string $id): Response
    {
        // TODO: to activate when login works correctly
        // if (!$this->authService->isAuthenticated()) {
        //     return Response::redirect($_ENV['APP_URL'] . '/login');
        // }

        $currentUser = $this->authService->getCurrentUser();
        $data = $request->getParsedBody();

        try {
            $this->eventService->update($id, $data);

            $html = $this->twig->render('events/edit.twig', [
                'success' => 'Event updated successfully!',
                'data'  => $data,
                'currentUser' => $currentUser
            ]);
            return Response::html($html);

        } catch (Exception $e) {
            $html = $this->twig->render('events/edit.twig', [
                'error' => $e->getMessage(),
                'event' => $data,
                'currentUser' => $currentUser
            ]);

            return Response::html($html, 400);
        }
    }

    /**
     * Delete event
     */
    public function deleteEvent(string $id): Response
    {
        // TODO: to activate when login works correctly
        // if (!$this->authService->isAuthenticated()) {
        //     return Response::redirect($_ENV['APP_URL'] . '/login');
        // }

        $currentUser = $this->authService->getCurrentUser();
        try {
            $this->eventService->delete($id);
        
            // Aggiungi un messaggio di successo nella sessione
            $_SESSION['success'] = 'Event deleted successfully!';

            // Redirigi alla pagina degli eventi
            return Response::redirect('/events'); 
            
        } catch (Exception $e) {
            return Response::html($e->getMessage(), 400);
        }
    }
}
