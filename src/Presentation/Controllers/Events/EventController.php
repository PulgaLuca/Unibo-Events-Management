<?php

declare(strict_types=1);

namespace App\Presentation\Controllers\Events;

use App\Application\Services\Events\EventService;
use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;
use Exception;
use Twig\Environment;

class EventController
{
    private EventService $eventService;
    private Environment $twig;

    public function __construct(EventService $eventService, Environment $twig)
    {
        $this->eventService = $eventService;
        $this->twig = $twig;
    }

    /**
     * List all events
     */
    public function index(Request $request): Response
    {
        $events = $this->eventService->findAll();

        $html = $this->twig->render('events/index.twig', [
            'events' => $events
        ]);

        return Response::html($html);
    }

    /**
     * Show create event form
     */
    public function createEvent(Request $request): Response
    {
        $html = $this->twig->render('events/create.twig');
        return Response::html($html);
    }

    /**
     * Handle create event submission
     */
    public function storeEvent(Request $request): Response
    {
        $data = $request->getParsedBody();

        try {
            $this->eventService->create($data);

            return Response::redirect($_ENV['APP_URL'] . '/events');
        } catch (Exception $e) {
            $html = $this->twig->render('events/create.twig', [
                'error' => $e->getMessage(),
                'data'  => $data
            ]);

            return Response::html($html, 400);
        }
    }

    /**
     * Show single event
     */
    public function showEvent(Request $request, string $id): Response
    {
        try {
            $event = $this->eventService->findById($id);

            $html = $this->twig->render('events/show.twig', [
                'event' => $event
            ]);

            return Response::html($html);
        } catch (Exception $e) {
            return Response::html('Event not found', 404);
        }
    }

    /**
     * Show edit event form
     */
    public function editEvent(Request $request, string $id): Response
    {
        try {
            $event = $this->eventService->findById($id);

            $html = $this->twig->render('events/edit.twig', [
                'event' => $event
            ]);

            return Response::html($html);
        } catch (Exception $e) {
            return Response::html('Event not found', 404);
        }
    }

    /**
     * Handle update event submission
     */
    public function updateEvent(Request $request, string $id): Response
    {
        $data = $request->getParsedBody();

        try {
            $this->eventService->update($id, $data);

            return Response::redirect($_ENV['APP_URL'] . '/events');
        } catch (Exception $e) {
            $html = $this->twig->render('events/edit.twig', [
                'error' => $e->getMessage(),
                'event' => $data
            ]);

            return Response::html($html, 400);
        }
    }

    /**
     * Delete event
     */
    public function deleteEvent(Request $request, string $id): Response
    {
        try {
            $this->eventService->delete($id);

            return Response::redirect($_ENV['APP_URL'] . '/events');
        } catch (Exception $e) {
            return Response::html($e->getMessage(), 400);
        }
    }
}
