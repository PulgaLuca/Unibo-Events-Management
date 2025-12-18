<?php

namespace App\Domain\Repositories\Events;

use App\Domain\Entities\Events\Event;

interface IEventRepository {
    public function findAll($limit = 100, $offset = 0);
    public function findById($eventId);
    public function findByStatus($status);
    public function create(Event $event);
    public function update(Event $event);
    public function delete($eventId);
    public function addTag($eventId, $tagId);
    public function removeTag($eventId, $tagId);
    public function getEventTags($eventId);
    public function countParticipants($eventId);
}

?>