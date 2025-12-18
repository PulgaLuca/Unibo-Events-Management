<?php

namespace App\Application\Services\Events;

use App\Domain\Entities\Events\Event;
use App\Domain\Repositories\Events\IEventRepository;

class CreateEvent {
    private $eventRepository;
    
    public function __construct(IEventRepository $eventRepository) {
        $this->eventRepository = $eventRepository;
    }
    
    public function execute($data) {
        $errors = $this->validate($data);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        $event = new Event();
        $event->setTitle($data['title']);
        $event->setDescription($data['description'] ?? null);
        $event->setStartDate($data['start_date']);
        $event->setEndDate($data['end_date'] ?? null);
        $event->setLocation($data['location'] ?? null);
        $event->setUrl($data['url'] ?? null);
        $event->setRegistrationDeadline($data['registration_deadline'] ?? null);
        $event->setMinParticipants($data['min_participants'] ?? 0);
        $event->setMaxParticipants($data['max_participants'] ?? null);
        $event->setStatus($data['status'] ?? 'Draft');
        $event->setTypeId($data['type_id']);
        $event->setParticipationTypeId($data['participation_type_id']);
        $event->setCreatorUserId($data['creator_user_id'] ?? null);
        $event->setCreatorTeamId($data['creator_team_id'] ?? null);
        
        $result = $this->eventRepository->create($event);
        
        if ($result) {
            return ['success' => true, 'event' => $result];
        }
        
        return ['success' => false, 'errors' => ['database' => 'Failed to create event']];
    }
    
    private function validate($data) {
        $errors = [];
        
        if (empty($data['title'])) {
            $errors['title'] = 'Title is required';
        }
        
        if (empty($data['start_date'])) {
            $errors['start_date'] = 'Start date is required';
        }
        
        if (empty($data['type_id'])) {
            $errors['type_id'] = 'Event type is required';
        }
        
        if (empty($data['participation_type_id'])) {
            $errors['participation_type_id'] = 'Participation type is required';
        }
        
        if (!empty($data['end_date']) && !empty($data['start_date'])) {
            if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
                $errors['end_date'] = 'End date must be after start date';
            }
        }
        
        if (isset($data['min_participants']) && isset($data['max_participants'])) {
            if ($data['max_participants'] < $data['min_participants']) {
                $errors['max_participants'] = 'Max participants must be greater than min participants';
            }
        }
        
        return $errors;
    }
}
?>