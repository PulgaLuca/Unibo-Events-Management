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
    public function showEventMainPage(): Response
    {
        // TODO: to activate when login works correctly
        // if (!$this->authService->isAuthenticated()) {
        //     return Response::redirect($_ENV['APP_URL'] . '/login');
        // }

        $currentUser = $this->authService->getCurrentUser();
        $events = $this->eventService->findAll();
        $eventTypes = $this->eventService->getEventTypes();
        $participationTypes = $this->eventService->getParticipationTypes();
        
        $html = $this->twig->render('eventIndex.twig', [
            'events' => $events,
            'success' => $_SESSION['success'] ?? null, // Utile come workaround in quanto delete esegue una POST ma poi il deleteEvent deve fare un redirect su /events
            'error' => $_SESSION['error'] ?? null,
            'currentUser' => $currentUser,
            'eventTypes' => $eventTypes,
            'participationTypes' => $participationTypes
        ]);

        // Rimozione del messaggio di successo dalla sessione dopo averlo passato alla view.
        $_SESSION['success'] = null;
        $_SESSION['error'] = null;

        return Response::html($html);
    }

    /**
     * Show create event form
     */
    public function showEventCreatePage(): Response
    {
        // TODO: to activate when login works correctly
        // if (!$this->authService->isAuthenticated()) {
        //     return Response::redirect($_ENV['APP_URL'] . '/login');
        // }

        $currentUser = $this->authService->getCurrentUser();
        $eventTypes = $this->eventService->getEventTypes();
        $participationTypes = $this->eventService->getParticipationTypes();

        $html = $this->twig->render('eventCreate.twig', [
            'currentUser' => $currentUser,
            'eventTypes' => $eventTypes,
            'participationTypes' => $participationTypes
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
        $eventTypes = $this->eventService->getEventTypes();
        $participationTypes = $this->eventService->getParticipationTypes();

        try 
        {
            $this->eventService->create($data);
            $_SESSION['success'] = 'Event created successfully!';
            
            $html = $this->twig->render('eventCreate.twig', [
                'success' => $_SESSION['success'],
                'data'  => $data,
                'currentUser' => $currentUser,
                'eventTypes' => $eventTypes,
                'participationTypes' => $participationTypes
            ]);
            return Response::html($html);

        } 
        catch (Exception $e) 
        {
            $html = $this->twig->render('eventCreate.twig', [
                'error' => $e->getMessage(),
                'data'  => $data,
                'currentUser' => $currentUser
            ]);

            $_SESSION['error'] = 'Something went wrong while creating this event: ' . $e->getMessage();
            
            return Response::html($html);
        }
    }

    /**
     * Show single event
     */
    public function showEventDetails(string $id): Response
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
                'currentUser' => $currentUser
            ]);

            return Response::html($html);
        } 
        catch (Exception $e) 
        {
            $_SESSION['error'] = 'Something went wrong while searching this event: ' . $e->getMessage();
            
            return Response::redirect('/events');
        }
    }

    /**
     * Show edit event form
     */
    public function showEventEditPage(string $id): Response
    {
        // TODO: to activate when login works correctly
        // if (!$this->authService->isAuthenticated()) {
        //     return Response::redirect($_ENV['APP_URL'] . '/login');
        // }

        $event = $this->eventService->findById($id);
        $currentUser = $this->authService->getCurrentUser();
        $eventTypes = $this->eventService->getEventTypes();
        $participationTypes = $this->eventService->getParticipationTypes();

        $html = $this->twig->render('eventEdit.twig', [
           'success' => $_SESSION['success'] ?? null,
            'event' => $event,
            'eventTypes' => $eventTypes,
            'participationTypes' => $participationTypes,
            'currentUser' => $currentUser
        ]);

        // Rimozione del messaggio di successo dalla sessione dopo averlo passato alla view.
        $_SESSION['success'] = null;
        $_SESSION['error'] = null;

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

        try 
        {
            $this->eventService->update($id, $event);
            $_SESSION['success'] = 'Event updated successfully!';
            
            return Response::redirect('/events');

        } 
        catch (Exception $e) 
        {
            $_SESSION['error'] = 'Something went wrong while updating this event: ' . $e->getMessage();
            
            $html = $this->twig->render('eventEdit.twig', [
                'error' => $_SESSION['error'],
                'event' => $event,
                'currentUser' => $currentUser
            ]);

            return Response::redirect('/events');
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

        try 
        {    
            $this->eventService->delete($id);
            $_SESSION['success'] = 'Event deleted successfully!';
            
            return Response::redirect('/events');

        } 
        catch (Exception $e) 
        {
            $_SESSION['error'] = 'Something went wrong while deleting this event: ' . $e->getMessage();
            return Response::redirect('/events');
        }
    }
}
