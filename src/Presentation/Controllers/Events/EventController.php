<?php

namespace App\Presentation\Controllers\Events;

use App\Application\Services\Events\CreateEvent;
use App\Infrastructure\Persistence\Mysql\Events\EventRepository;

class EventController {
    private $eventRepository;
    private $eventTypeRepository;
    
    public function __construct($database) {
        $this->eventRepository = new EventRepository($database);
        // $this->eventTypeRepository = new EventTypeRepository($database);
    }
    
    // public function index() {
    //     $useCase = new ListEventsUseCase($this->eventRepository);
        
    //     $status = $_GET['status'] ?? null;
    //     $result = $useCase->execute(['status' => $status]);
        
    //     $events = $result['events'];
        
    //     require __DIR__ . '/../Views/events/index.php';
    // }
    
    // public function show($eventId) {
    //     $useCase = new GetEventUseCase($this->eventRepository);
    //     $result = $useCase->execute($eventId);
        
    //     if (!$result['success']) {
    //         http_response_code(404);
    //         echo '<h1>Event not found</h1>';
    //         return;
    //     }
        
    //     $event = $result['event'];
    //     $participantCount = $result['participant_count'];
        
    //     require __DIR__ . '/../Views/events/show.php';
    // }
    
    public function create() {
        $eventTypes = $this->eventTypeRepository->findAll();
    }
    
    public function store() {
        $useCase = new CreateEvent($this->eventRepository);
        
        $data = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'start_date' => $_POST['start_date'] ?? '',
            'end_date' => $_POST['end_date'] ?? null,
            'location' => $_POST['location'] ?? '',
            'url' => $_POST['url'] ?? '',
            'registration_deadline' => $_POST['registration_deadline'] ?? null,
            'min_participants' => $_POST['min_participants'] ?? 0,
            'max_participants' => $_POST['max_participants'] ?? null,
            'status' => $_POST['status'] ?? 'Draft',
            'type_id' => $_POST['type_id'] ?? '',
            'participation_type_id' => $_POST['participation_type_id'] ?? '',
            'creator_user_id' => $_SESSION['user_id'] ?? null
        ];
        
        $result = $useCase->execute($data);
        
        if ($result['success']) {
            header('Location: /events/' . $result['event']->getEventId());
            exit;
        } else {
            $errors = $result['errors'];
            $eventTypes = $this->eventTypeRepository->findAll();
        }
    }
    
    // public function edit($eventId) {
    //     $useCase = new GetEventUseCase($this->eventRepository);
    //     $result = $useCase->execute($eventId);
        
    //     if (!$result['success']) {
    //         http_response_code(404);
    //         echo '<h1>Event not found</h1>';
    //         return;
    //     }
        
    //     $event = $result['event'];
    //     $eventTypes = $this->eventTypeRepository->findAll();
        
    //     require __DIR__ . '/../Views/events/edit.php';
    // }
    
    // public function update($eventId) {
    //     $useCase = new UpdateEventUseCase($this->eventRepository);
        
    //     $data = [
    //         'title' => $_POST['title'] ?? '',
    //         'description' => $_POST['description'] ?? '',
    //         'start_date' => $_POST['start_date'] ?? '',
    //         'end_date' => $_POST['end_date'] ?? null,
    //         'location' => $_POST['location'] ?? '',
    //         'url' => $_POST['url'] ?? '',
    //         'registration_deadline' => $_POST['registration_deadline'] ?? null,
    //         'min_participants' => $_POST['min_participants'] ?? 0,
    //         'max_participants' => $_POST['max_participants'] ?? null,
    //         'status' => $_POST['status'] ?? 'Draft',
    //         'type_id' => $_POST['type_id'] ?? '',
    //         'participation_type_id' => $_POST['participation_type_id'] ?? ''
    //     ];
        
    //     $result = $useCase->execute($eventId, $data);
        
    //     if ($result['success']) {
    //         header('Location: /events/' . $eventId);
    //         exit;
    //     } else {
    //         $errors = $result['errors'];
    //         $event = $result['event'] ?? null;
    //         $eventTypes = $this->eventTypeRepository->findAll();
    //         require __DIR__ . '/../Views/events/edit.php';
    //     }
    // }
    
    // public function apiList() {
    //     $useCase = new ListEventsUseCase($this->eventRepository);
    //     $result = $useCase->execute();
        
    //     $events = array_map(function($event) {
    //         return $event->toArray();
    //     }, $result['events']);
        
    //     echo json_encode(['success' => true, 'data' => $events]);
    // }
}

?>