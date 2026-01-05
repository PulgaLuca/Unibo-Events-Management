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
        
        $html = $this->twig->render('eventIndex.twig', [
            'events' => $events,
            'success' => $_SESSION['success'] ?? null, // Utile come workaround in quanto delete esegue una POST ma poi il deleteEvent deve fare un redirect su /events
            'currentUser' => $currentUser
        ]);

        // Rimozione del messaggio di successo dalla sessione dopo averlo passato alla view.
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

        $html = $this->twig->render('eventCreate.twig', [
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

            $html = $this->twig->render('eventCreate.twig', [
                'success' => 'Event created successfully!',
                'data'  => $data,
                'currentUser' => $currentUser
            ]);
            return Response::html($html);

        } catch (Exception $e) {
            $html = $this->twig->render('eventCreate.twig', [
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

            $html = $this->twig->render('eventShow.twig', [
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

        $html = $this->twig->render('eventEdit.twig', [
           'success' => $_SESSION['success'] ?? null,
            'event' => $event,
            'eventTypes' => $this->eventService->getEventTypes(),
            'participationTypes' => $this->eventService->getParticipationTypes(),
            'currentUser' => $currentUser
        ]);

        // Rimozione del messaggio di successo dalla sessione dopo averlo passato alla view.
        $_SESSION['success'] = null;

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
        $event = $request->getParsedBody();

        try {
            $this->eventService->update($id, $event);

            $_SESSION['success'] = 'Event updated successfully!';
            
            return Response::redirect('/events'); 
        } catch (Exception $e) {
            $html = $this->twig->render('eventEdit.twig', [
                'error' => $e->getMessage(),
                'event' => $event,
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

    /**
     * Gestisce validazione server-side, rinomina e spostamento file
     */
    private function handleImageUpload($uploadedFile, string $eventTitle): string
    {
        // A. Validazione Server-Side
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if (!in_array($uploadedFile->getMimeType(), $allowedMimeTypes)) {
            throw new Exception("Formato immagine non valido (solo JPG, PNG, WEBP).");
        }

        if ($uploadedFile->getSize() > $maxSize) {
            throw new Exception("L'immagine supera i 2MB.");
        }

        // B. Definizione Percorso e Nome File
        $destinationPath = $_SERVER['DOCUMENT_ROOT'] . '/assets/images/events/';
        
        // Creazione Slug dal titolo per il nome file (es. "My Event" -> "my-event")
        // Se non hai una libreria slugger, usa una funzione semplice:
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $eventTitle)));
        
        // Recupera estensione (es. jpg)
        $extension = $uploadedFile->guessExtension() ?? 'jpg';
        
        $newFilename = $slug . '.' . $extension;

        // C. Spostamento File
        // Assicurati che la cartella esista e sia scrivibile
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $uploadedFile->move($destinationPath, $newFilename);

        // Ritorna il percorso relativo per il database
        return '/assets/images/events/' . $newFilename;
    }
}
