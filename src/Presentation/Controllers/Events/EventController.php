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

    public function showEventMainPage(): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::redirect($_ENV['APP_URL'] . '/login');
        }

        $currentUser = $this->authService->getCurrentUser();
        $events = $this->eventService->findAll();
        $eventsWithContext = $this->eventService->enrichEventsForUser($events, $currentUser->id);

        $html = $this->twig->render('eventIndex.twig', [
            'events' => $eventsWithContext,
            'currentUser' => $currentUser,
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null
        ]);

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
        $eventTypes = $this->eventService->getEventTypes();
        $participationTypes = $this->eventService->getParticipationTypes();

        $html = $this->twig->render('eventCreate.twig', [
            'eventTypes' => $eventTypes,
            'participationTypes' => $participationTypes,
            'currentUser' => $currentUser,
            'organizer' => $currentUser,
            'isCreator' => true,
            'isSubscribed' => true,
            'userRole' => 'Lead',
            'success' => $_SESSION['success'] ?? null,
            'error' => $_SESSION['error'] ?? null
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
            $this->eventService->create($data, $currentUser->id);
            $_SESSION['success'] = 'Event created successfully!';
            
            $html = $this->twig->render('eventCreate.twig', [
                'success' => $_SESSION['success'],
                'data'  => $data,
                'currentUser' => $currentUser,
                'eventTypes' => $eventTypes,
                'participationTypes' => $participationTypes,
                'organizer' => $currentUser,
                'isCreator' => true,
                'isSubscribed' => true,
                'userRole' => 'Lead'
            ]);
            return Response::html($html);
        }
        catch (Exception $e) 
        {
            $html = $this->twig->render('eventCreate.twig', [
                'error' => $e->getMessage(),
                'data'  => $data,
                'currentUser' => $currentUser,
                'eventTypes' => $eventTypes,
                'participationTypes' => $participationTypes,
                'organizer' => $currentUser,
                'isCreator' => true,
                'isSubscribed' => true,
                'userRole' => 'Lead'
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
        if (!$this->authService->isAuthenticated()) {
            return Response::redirect($_ENV['APP_URL'] . '/login');
        }

        try {
            $event = $this->eventService->findById($id);

            if (!$event) {
                $_SESSION['error'] = 'Event not found';
                return Response::redirect('/events');
            }

            $currentUser = $this->authService->getCurrentUser();
            $organizer = $this->eventService->getEventCreator($id);
            $eventTypes = $this->eventService->getEventTypes();
            $participationTypes = $this->eventService->getParticipationTypes();

            $isCreator = $event->getCreatorUserId() === $currentUser->id;
            $isSubscribed = $this->eventService->isUserSubscribed($id, $currentUser->id);
            $userRole = $this->eventService->resolveUserRoleInEvent($id, $currentUser->id);

            $html = $this->twig->render('eventShow.twig', [
                'data' => $event,
                'eventTypes' => $eventTypes,
                'participationTypes' => $participationTypes,
                'currentUser' => $currentUser,
                'organizer' => $organizer,
                'isCreator' => $isCreator,
                'isSubscribed' => $isSubscribed,
                'userRole' => $userRole,
                'success' => $_SESSION['success'] ?? null,
                'error' => $_SESSION['error'] ?? null
            ]);

            $_SESSION['success'] = null;
            $_SESSION['error'] = null;

            return Response::html($html);
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            return Response::redirect('/events');
        }
    }


    /**
     * Show edit event form
     */
    // public function showEventEditPage(string $id): Response
    // {
    //     if (!$this->authService->isAuthenticated()) {
    //         return Response::redirect($_ENV['APP_URL'] . '/login');
    //     }

    //     $data = $this->eventService->findById($id);
    //     $currentUser = $this->authService->getCurrentUser();
    //     $eventTypes = $this->eventService->getEventTypes();
    //     $participationTypes = $this->eventService->getParticipationTypes();

    //     // Verifica se l'utente è iscritto e se è il creatore
    //     $isSubscribed = $this->eventService->isUserSubscribed($id, $currentUser->id);
    //     $isCreator = ($data->getCreatorUserId() === $currentUser->id);
    //     error_log(print_r('isSubscribed: ' . $isSubscribed, true));
    //     error_log(print_r('isCreator: ' . $isCreator, true));

    //     if (!$isCreator) {
    //         $_SESSION['error'] = 'Unauthorized action';
    //         return Response::redirect($_ENV['APP_URL'] . '/events');
    //     }

    //     $html = $this->twig->render('eventEdit.twig', [
    //        'success' => $_SESSION['success'] ?? null,
    //         'data' => $data,
    //         'eventTypes' => $eventTypes,
    //         'participationTypes' => $participationTypes,
    //         'currentUser' => $currentUser,
    //         'isSubscribed' => $isSubscribed,
    //         'isCreator' => $isCreator
    //     ]);

    //     // Rimozione del messaggio di successo dalla sessione dopo averlo passato alla view.
    //     $_SESSION['success'] = null;
    //     $_SESSION['error'] = null;

    //     return Response::html($html);
    // }

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
        
        $isSubscribed = $this->eventService->isUserSubscribed($id, $currentUser->id);
        $isCreator = ($data['creator_user_id'] === $currentUser->id);
        
        error_log(print_r($data, true));
        error_log(print_r($isSubscribed, true));
        error_log(print_r($isCreator, true));

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

        $isSubscribed = $this->eventService->isUserSubscribed($id, $currentUser->id);
        $isCreator = ($data->getCreatorUserId() === $currentUser->id);
        
        error_log(print_r($data, true));
        error_log(print_r($currentUser, true));
        error_log(print_r($isSubscribed, true));
        error_log(print_r($isCreator, true));


        if (!$isCreator) {
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
