<?php

namespace App\Infrastructure\Persistence\Mysql\Events;

use App\Domain\Entities\Events\Event;
use App\Domain\Repositories\Events\IEventRepository;

class EventRepository implements IEventRepository
{
    private $db;
    
    public function __construct($database) {
        $this->db = $database->getConnection();
    }
    
    public function findAll($limit = 100, $offset = 0) {
        $query = "SELECT e.*, 
                         et.name as event_type_name,
                         pt.name as participation_type_name
                  FROM EVENT e
                  LEFT JOIN EVENT_TYPE et ON e.type_id = et.type_id
                  LEFT JOIN PARTICIPATION_TYPE pt ON e.participation_type_id = pt.type_id
                  ORDER BY e.start_date DESC
                  LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $this->mapToEntity($row);
        }
        
        return $events;
    }
    
    public function findById($eventId) {
        $query = "SELECT e.*, 
                         et.name as event_type_name,
                         pt.name as participation_type_name
                  FROM EVENT e
                  LEFT JOIN EVENT_TYPE et ON e.type_id = et.type_id
                  LEFT JOIN PARTICIPATION_TYPE pt ON e.participation_type_id = pt.type_id
                  WHERE e.event_id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $this->mapToEntity($row);
        }
        
        return null;
    }
    
    public function findByStatus($status) {
        $query = "SELECT e.*, 
                         et.name as event_type_name,
                         pt.name as participation_type_name
                  FROM EVENT e
                  LEFT JOIN EVENT_TYPE et ON e.type_id = et.type_id
                  LEFT JOIN PARTICIPATION_TYPE pt ON e.participation_type_id = pt.type_id
                  WHERE e.status = ?
                  ORDER BY e.start_date DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $this->mapToEntity($row);
        }
        
        return $events;
    }
    
    public function create(Event $event) {
        $query = "INSERT INTO EVENT (event_id, title, description, start_date, end_date, 
                                     location, url, registration_deadline, min_participants, 
                                     max_participants, status, type_id, participation_type_id,
                                     creator_user_id, creator_team_id)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($query);
        
        $eventId = $this->generateUuid();
        $title = $event->getTitle();
        $description = $event->getDescription();
        $startDate = $event->getStartDate();
        $endDate = $event->getEndDate();
        $location = $event->getLocation();
        $url = $event->getUrl();
        $regDeadline = $event->getRegistrationDeadline();
        $minPart = $event->getMinParticipants();
        $maxPart = $event->getMaxParticipants();
        $status = $event->getStatus();
        $typeId = $event->getTypeId();
        $partTypeId = $event->getParticipationTypeId();
        $creatorUserId = $event->getCreatorUserId();
        $creatorTeamId = $event->getCreatorTeamId();
        
        $stmt->bind_param("ssssssssiisssss", 
            $eventId, $title, $description, $startDate, $endDate,
            $location, $url, $regDeadline, $minPart, $maxPart,
            $status, $typeId, $partTypeId, $creatorUserId, $creatorTeamId
        );
        
        if ($stmt->execute()) {
            $event->setEventId($eventId);
            return $event;
        }
        
        return false;
    }
    
    public function update(Event $event) {
        $query = "UPDATE EVENT SET 
                    title = ?, description = ?, start_date = ?, end_date = ?,
                    location = ?, url = ?, registration_deadline = ?,
                    min_participants = ?, max_participants = ?, status = ?,
                    type_id = ?, participation_type_id = ?
                  WHERE event_id = ?";
        
        $stmt = $this->db->prepare($query);
        
        $title = $event->getTitle();
        $description = $event->getDescription();
        $startDate = $event->getStartDate();
        $endDate = $event->getEndDate();
        $location = $event->getLocation();
        $url = $event->getUrl();
        $regDeadline = $event->getRegistrationDeadline();
        $minPart = $event->getMinParticipants();
        $maxPart = $event->getMaxParticipants();
        $status = $event->getStatus();
        $typeId = $event->getTypeId();
        $partTypeId = $event->getParticipationTypeId();
        $eventId = $event->getEventId();
        
        $stmt->bind_param("sssssssiiisss",
            $title, $description, $startDate, $endDate, $location,
            $url, $regDeadline, $minPart, $maxPart, $status,
            $typeId, $partTypeId, $eventId
        );
        
        return $stmt->execute();
    }
    
    public function delete($eventId) {
        $query = "DELETE FROM EVENT WHERE event_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $eventId);
        return $stmt->execute();
    }
    
    public function addTag($eventId, $tagId) {
        $query = "INSERT INTO EVENT_TAG (event_id, tag_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ss", $eventId, $tagId);
        return $stmt->execute();
    }
    
    public function removeTag($eventId, $tagId) {
        $query = "DELETE FROM EVENT_TAG WHERE event_id = ? AND tag_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ss", $eventId, $tagId);
        return $stmt->execute();
    }
    
    public function getEventTags($eventId) {
        $query = "SELECT t.* FROM TAG t
                  INNER JOIN EVENT_TAG et ON t.tag_id = et.tag_id
                  WHERE et.event_id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $tags = [];
        while ($row = $result->fetch_assoc()) {
            $tags[] = $row;
        }
        
        return $tags;
    }
    
    public function countParticipants($eventId) {
        $query = "SELECT COUNT(*) as count FROM EVENT_PARTICIPATION WHERE event_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    
    private function mapToEntity($row) {
        $event = new Event(
            $row['event_id'],
            $row['title'],
            $row['description'] ?? null,
            $row['start_date'],
            $row['end_date'] ?? null,
            $row['location'] ?? null,
            $row['url'] ?? null,
            $row['registration_deadline'] ?? null,
            $row['min_participants'],
            $row['max_participants'] ?? null,
            $row['status'],
            $row['type_id'],
            $row['participation_type_id'],
            $row['creator_user_id'] ?? null,
            $row['creator_team_id'] ?? null
        );
        
        return $event;
    }

    
    private function generateUuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}

?>
