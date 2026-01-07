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
        if (!$this->authService->isAuthenticated()) {
            return Response::redirect($_ENV['APP_URL'] . '/login');
        }

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
        if (!$this->authService->isAuthenticated()) {
            return Response::redirect($_ENV['APP_URL'] . '/login');
        }

        $currentUser = $this->authService->getCurrentUser();
        // error_log(print_r($currentUser, true));
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
        if (!$this->authService->isAuthenticated()) {
            return Response::redirect($_ENV['APP_URL'] . '/login');
        }

        $data = $request->getParsedBody();
        $currentUser = $this->authService->getCurrentUser();
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
            if (!$this->authService->isAuthenticated()) {
                return Response::redirect($_ENV['APP_URL'] . '/login');
            }

            $data = $this->eventService->findById($id);
            $currentUser = $this->authService->getCurrentUser();
            $eventTypes = $this->eventService->getEventTypes();
            $participationTypes = $this->eventService->getParticipationTypes();

            // Verifica se l'utente è iscritto e se è il creatore
            $isSubscribed = $this->eventService->isUserSubscribed($id, $currentUser->id);
            $isCreator = ($data->getCreatorUserId() === $currentUser->id);

            $html = $this->twig->render('eventShow.twig', [
                'data' => $data,
                'eventTypes' => $eventTypes,
                'participationTypes' => $participationTypes,
                'currentUser' => $currentUser,
                'isSubscribed' => $isSubscribed,
                'isCreator' => $isCreator
            ]);

            return Response::html($html);
        }
        catch (Exception $e) 
        {
            $_SESSION['error'] = 'Something went wrong while searching this event: ' . $e->getMessage();
            
            return Response::redirect($_ENV['APP_URL'] . '/events');
        }
    }

    /**
     * Show edit event form
     */
    public function showEventEditPage(string $id): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::redirect($_ENV['APP_URL'] . '/login');
        }

        $data = $this->eventService->findById($id);
        $currentUser = $this->authService->getCurrentUser();
        $eventTypes = $this->eventService->getEventTypes();
        $participationTypes = $this->eventService->getParticipationTypes();

        // Verifica se l'utente è iscritto e se è il creatore
        $isSubscribed = $this->eventService->isUserSubscribed($id, $currentUser->id);
        $isCreator = ($data->getCreatorUserId() === $currentUser->id);

        if (!$isCreator) {
            $_SESSION['error'] = 'Unauthorized action';
            return Response::redirect($_ENV['APP_URL'] . '/events');
        }

        $html = $this->twig->render('eventEdit.twig', [
           'success' => $_SESSION['success'] ?? null,
            'data' => $data,
            'eventTypes' => $eventTypes,
            'participationTypes' => $participationTypes,
            'currentUser' => $currentUser,
            'isSubscribed' => $isSubscribed,
            'isCreator' => $isCreator
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
        if (!$this->authService->isAuthenticated()) {
            return Response::redirect($_ENV['APP_URL'] . '/login');
        }

        $currentUser = $this->authService->getCurrentUser();
        $data = $request->getParsedBody();

        // Verifica se l'utente è iscritto e se è il creatore
        $isSubscribed = $this->eventService->isUserSubscribed($id, $currentUser->id);
        $isCreator = ($data['creator_user_id'] === $currentUser->id);

        if (!$isCreator) {
            $_SESSION['error'] = 'Unauthorized action';
            return Response::redirect($_ENV['APP_URL'] . '/events');
        }

        try 
        {
            $this->eventService->update($id, $data);
            $_SESSION['success'] = 'Event updated successfully!';
            
            return Response::redirect($_ENV['APP_URL'] . '/events');

        } 
        catch (Exception $e) 
        {
            $_SESSION['error'] = 'Something went wrong while updating this event: ' . $e->getMessage();
            
            $html = $this->twig->render('eventEdit.twig', [
                'error' => $_SESSION['error'],
                'event' => $data,
                'currentUser' => $currentUser
            ]);

            return Response::redirect($_ENV['APP_URL'] . '/events');
        }
    }

    /**
     * Delete event
     */
    public function deleteEvent(string $id): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::redirect($_ENV['APP_URL'] . '/login');
        }

        $data = $this->eventService->findById($id);
        $currentUser = $this->authService->getCurrentUser();

        // Verifica se l'utente è iscritto e se è il creatore
        $isSubscribed = $this->eventService->isUserSubscribed($id, $currentUser->id);
        $isCreator = ($data->getCreatorUserId() === $currentUser->id);

        if ($isCreator) {
            $_SESSION['error'] = 'Unauthorized action';
            return Response::redirect($_ENV['APP_URL'] . '/events');
        }

        try 
        {    
            $this->eventService->delete($id);
            $_SESSION['success'] = 'Event deleted successfully!';
            
            return Response::redirect($_ENV['APP_URL'] . '/events');
        } 
        catch (Exception $e) 
        {
            $_SESSION['error'] = 'Something went wrong while deleting this event: ' . $e->getMessage();
            return Response::redirect($_ENV['APP_URL'] . '/events');
        }
    }

    /**
     * Subscribe user to event
     */
    public function subscribeToEvent(string $id): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::redirect($_ENV['APP_URL'] . '/login');
        }

        try {
            $currentUser = $this->authService->getCurrentUser();
            $this->eventService->subscribeUser($id, $currentUser['id']);
            $_SESSION['success'] = 'Successfully subscribed to the event!';
            
            return Response::redirect('/events/' . $id);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Something went wrong while subscribing: ' . $e->getMessage();
            return Response::redirect('/events/' . $id);
        }
    }

    /**
     * Unsubscribe user from event
     */
    public function unsubscribeFromEvent(string $id): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::redirect($_ENV['APP_URL'] . '/login');
        }

        try {
            $currentUser = $this->authService->getCurrentUser();
            $this->eventService->unsubscribeUser($id, $currentUser['id']);
            $_SESSION['success'] = 'Successfully unsubscribed from the event!';
            
            return Response::redirect('/events/' . $id);
        } catch (Exception $e) {
            $_SESSION['error'] = 'Something went wrong while unsubscribing: ' . $e->getMessage();
            return Response::redirect('/events/' . $id);
        }
    }
}
