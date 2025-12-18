<?php

namespace App\Domain\Entities\Events;

class Event {
    private $eventId;
    private $title;
    private $description;
    private $startDate;
    private $endDate;
    private $location;
    private $url;
    private $registrationDeadline;
    private $minParticipants;
    private $maxParticipants;
    private $status;
    private $typeId;
    private $participationTypeId;
    private $creatorUserId;
    private $creatorTeamId;
    
    private $eventType;
    private $participationType;
    private $tags = [];
    private $requiredSkills = [];
    private $participations = [];
    
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }
    
    private function hydrate($data) {
        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }
    
    // Getters
    public function getEventId() { return $this->eventId; }
    public function getTitle() { return $this->title; }
    public function getDescription() { return $this->description; }
    public function getStartDate() { return $this->startDate; }
    public function getEndDate() { return $this->endDate; }
    public function getLocation() { return $this->location; }
    public function getUrl() { return $this->url; }
    public function getRegistrationDeadline() { return $this->registrationDeadline; }
    public function getMinParticipants() { return $this->minParticipants; }
    public function getMaxParticipants() { return $this->maxParticipants; }
    public function getStatus() { return $this->status; }
    public function getTypeId() { return $this->typeId; }
    public function getParticipationTypeId() { return $this->participationTypeId; }
    public function getCreatorUserId() { return $this->creatorUserId; }
    public function getCreatorTeamId() { return $this->creatorTeamId; }
    public function getEventType() { return $this->eventType; }
    public function getParticipationType() { return $this->participationType; }
    public function getTags() { return $this->tags; }
    public function getRequiredSkills() { return $this->requiredSkills; }
    public function getParticipations() { return $this->participations; }
    
    // Setters
    public function setEventId($eventId) { $this->eventId = $eventId; }
    public function setTitle($title) { $this->title = $title; }
    public function setDescription($description) { $this->description = $description; }
    public function setStartDate($startDate) { $this->startDate = $startDate; }
    public function setEndDate($endDate) { $this->endDate = $endDate; }
    public function setLocation($location) { $this->location = $location; }
    public function setUrl($url) { $this->url = $url; }
    public function setRegistrationDeadline($deadline) { $this->registrationDeadline = $deadline; }
    public function setMinParticipants($min) { $this->minParticipants = $min; }
    public function setMaxParticipants($max) { $this->maxParticipants = $max; }
    public function setStatus($status) { $this->status = $status; }
    public function setTypeId($typeId) { $this->typeId = $typeId; }
    public function setParticipationTypeId($id) { $this->participationTypeId = $id; }
    public function setCreatorUserId($id) { $this->creatorUserId = $id; }
    public function setCreatorTeamId($id) { $this->creatorTeamId = $id; }
    public function setEventType($type) { $this->eventType = $type; }
    public function setParticipationType($type) { $this->participationType = $type; }
    public function setTags($tags) { $this->tags = $tags; }
    public function setRequiredSkills($skills) { $this->requiredSkills = $skills; }
    public function setParticipations($participations) { $this->participations = $participations; }
    
    public function toArray() {
        return [
            'event_id' => $this->eventId,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'location' => $this->location,
            'url' => $this->url,
            'registration_deadline' => $this->registrationDeadline,
            'min_participants' => $this->minParticipants,
            'max_participants' => $this->maxParticipants,
            'status' => $this->status,
            'type_id' => $this->typeId,
            'participation_type_id' => $this->participationTypeId,
            'creator_user_id' => $this->creatorUserId,
            'creator_team_id' => $this->creatorTeamId
        ];
    }
}

?>
