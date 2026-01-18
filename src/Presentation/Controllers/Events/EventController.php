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

    public function showEventMainPage(Request $request): Response
    {
        if (!$this->authService->isAuthenticated()) {
            return Response::redirect('/login');
        }

        $currentUser = $this->authService->getCurrentUser();
        $filters = $request->getQueryParams();

        $events = $this->eventService->getEventsByFilters($filters, $currentUser);
        $eventsWithContext = $this->eventService->enrichEventsForUser($events, $currentUser->id);

        // AJAX -> restituisce solo la lista
        if ($request->isXmlHttpRequest()) {
            return new Response(
                $this->twig->render('partials/eventsList.twig', [
                    'events' => $eventsWithContext
                ])
            );
        }

        // Render pagina completa
        return new Response(
            $this->twig->render('eventIndex.twig', [
                'events' => $eventsWithContext,
                'filters' => $filters,
                'currentUser' => $currentUser
            ])
        );
    }


    /**
     * Show create event form
     */
    public function showEventCreatePage(): Response
    {
        try 
        {
            if (!$this->authService->isAuthenticated()) {
                return Response::redirect($_ENV['APP_URL'] . '/login');
            }

            $_SESSION['success'] = null;
            $_SESSION['error'] = null;

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
        catch (Exception $e)
        {
            $_SESSION['error'] = 'Something went wrong while loading create event page: ' . $e->getMessage();
            
            return Response::redirect($_ENV['APP_URL'] . '/events');
        }
    }

    /**
     * Handle create event submission
     */
    public function storeEvent(Request $request): Response
    {
        try
        {
            if (!$this->authService->isAuthenticated()) {
                return Response::redirect($_ENV['APP_URL'] . '/login');
            }

            $data = $request->getParsedBody();

            error_log(print_r($data, true));

            $currentUser = $this->authService->getCurrentUser();
            $eventTypes = $this->eventService->getEventTypes();
            $participationTypes = $this->eventService->getParticipationTypes();

            $this->eventService->create($data, $currentUser->id);
            $_SESSION['success'] = 'Event created successfully!';
            
            $eventsWithContext = $this->eventService->enrichEventsForUser($this->eventService->findAll(), $currentUser->id);

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

            $_SESSION['error'] = null;
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

            $event = $this->eventService->findById($id);

            if (!$event) {
                $_SESSION['error'] = 'Event not found';
                return Response::redirect('/events');
            }

            $currentUser = $this->authService->getCurrentUser();
            $organizer = $this->eventService->getEventCreator($id);
            $eventTypes = $this->eventService->getEventTypes();
            $participationTypes = $this->eventService->getParticipationTypes();
            $participants = $this->eventService->getEventParticipants($id);
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
                'participants' => $participants,
                'success' => $_SESSION['success'] ?? null,
                'error' => $_SESSION['error'] ?? null
            ]);

            $_SESSION['success'] = null;
            $_SESSION['error'] = null;

            return Response::html($html);

        } 
        catch (Exception $e) 
        {
            $_SESSION['error'] = null;
            $_SESSION['error'] = $e->getMessage();
            return Response::redirect('/events');
        }
    }

    /**
     * Handle update event submission
     */
    public function updateEvent(Request $request, string $id): Response
    {
        try 
        {
            if (!$this->authService->isAuthenticated()) {
                return Response::redirect($_ENV['APP_URL'] . '/login');
            }

            $currentUser = $this->authService->getCurrentUser();
            $data = $request->getParsedBody();

            error_log(print_r($data, true));

            $this->eventService->update($id, $data);
            $_SESSION['success'] = 'Event updated successfully!';
            
            return Response::redirect($_ENV['APP_URL'] . '/events');

        } 
        catch (Exception $e) 
        {
            $_SESSION['error'] = 'Something went wrong while updating this event: ' . $e->getMessage();
            
            $html = $this->twig->render('eventShow.twig', [
                'error' => 'Something went wrong: ' . $e->getMessage(),
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
        try 
        {
            if (!$this->authService->isAuthenticated()) {
                return Response::redirect($_ENV['APP_URL'] . '/login');
            }

            $data = $this->eventService->findById($id);
            $currentUser = $this->authService->getCurrentUser();

            $isSubscribed = $this->eventService->isUserSubscribed($id, $currentUser->id);
            $isCreator = ($data->getCreatorUserId() === $currentUser->id);

            if (!$isCreator) {
                $_SESSION['error'] = 'Unauthorized action';
                return Response::redirect($_ENV['APP_URL'] . '/events');
            }

            $this->eventService->delete($id);
            $_SESSION['success'] = 'Event deleted successfully!';
            
            return Response::redirect($_ENV['APP_URL'] . '/events');
        } 
        catch (Exception $e) 
        {
            $_SESSION['error'] = null;
            $_SESSION['error'] = 'Something went wrong while deleting this event: ' . $e->getMessage();
            return Response::redirect($_ENV['APP_URL'] . '/events');
        }
    }

    /**
     * Subscribe user to event
     */
    public function subscribeToEvent(string $id): Response
    {
        try 
        {
            if (!$this->authService->isAuthenticated()) {
                return Response::redirect($_ENV['APP_URL'] . '/login');
            }

            $currentUser = $this->authService->getCurrentUser();
            $this->eventService->subscribeUser($id, $currentUser->id, 'Participant');
            $_SESSION['success'] = 'Successfully subscribed to the event!';
            
            return Response::redirect('/events/' . $id);

        } 
        catch (Exception $e) 
        {
            $_SESSION['error'] = 'Something went wrong while subscribing: ' . $e->getMessage();
            return Response::redirect('/events/' . $id);
        }
    }

    /**
     * Unsubscribe user from event
     */
    public function unsubscribeFromEvent(string $id): Response
    {
        try 
        {
            if (!$this->authService->isAuthenticated()) {
                return Response::redirect($_ENV['APP_URL'] . '/login');
            }

            $currentUser = $this->authService->getCurrentUser();
            $this->eventService->unsubscribeUser($id, $currentUser->id);
            $_SESSION['success'] = 'Successfully unsubscribed from the event!';
            
            return Response::redirect('/events/' . $id);
        } 
        catch (Exception $e) 
        {
            $_SESSION['error'] = null;
            $_SESSION['error'] = 'Something went wrong while unsubscribing: ' . $e->getMessage();
            return Response::redirect('/events/' . $id);
        }
    }

    // public function filterEvents(Request $request): Response
    // {
    //     if (!$this->authService->isAuthenticated()) {
    //         return Response::redirect($_ENV['APP_URL'] . '/login');
    //     }

    //     $currentUser = $this->authService->getCurrentUser();
    //     $filters = $request->getQueryParams(); // q, country, city, status...
    //     $events = $this->eventService->filterEvents($filters);
    //     $eventsWithContext = $this->eventService->enrichEventsForUser($events, $currentUser->id);

    //     error_log(print_r($events,true));
    //     error_log(print_r($eventsWithContext,true));

    //     // ritorna solo la lista eventi renderizzata
    //     $html = $this->twig->render('partials/eventsList.twig', [
    //         'events' => $eventsWithContext
    //     ]);

    //     return Response::html($html);
    // }

    public function home(): Response
    {
        return new Response(
            $this->twig->render('home.twig')
        );
        return Response::html($html);
    }
}
