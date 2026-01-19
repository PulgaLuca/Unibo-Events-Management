<?php

declare(strict_types=1);

namespace App\Application\Services\Team;

use App\Domain\Entities\Team\Team;
use App\Domain\Entities\Team\TeamMembership;
use App\Domain\Repositories\Team\ITeamRepository;
use Exception;

class TeamService
{
    private ITeamRepository $teamRepository;

    public function __construct(ITeamRepository $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    /**
     * Create a new team with the creator as the leader
     */
    public function createTeam(int $creatorUserId, string $name, ?string $description, int $maxParticipants, int $minParticipants = 1): Team
    {
        if ($maxParticipants < $minParticipants) {
            throw new Exception("Maximum participants cannot be less than minimum participants");
        }

        if ($minParticipants < 1) {
            throw new Exception("Minimum participants must be at least 1");
        }

        $team = new Team();
        $team->name = $name;
        $team->description = $description;
        $team->max_participants = $maxParticipants;
        $team->min_participants = $minParticipants;
        $team->status = Team::STATUS_SEARCHING;

        $team = $this->teamRepository->create($team);

        // Add creator as leader
        $this->teamRepository->addMember($team->id, $creatorUserId, TeamMembership::STATUS_LEAD);

        // Check if team is full after adding leader
        $this->updateTeamStatusIfFull($team->id);

        return $team;
    }

    /**
     * Update team information
     */
    public function updateTeam(string $teamId, int $userId, string $name, ?string $description, int $maxParticipants, int $minParticipants): bool
    {
        if (!$this->teamRepository->isUserLeader($teamId, $userId)) {
            throw new Exception("Only team leaders can update team information");
        }

        $team = $this->teamRepository->findById($teamId);
        if (!$team) {
            throw new Exception("Team not found");
        }

        if ($maxParticipants < $minParticipants) {
            throw new Exception("Maximum participants cannot be less than minimum participants");
        }

        // Check if new max is less than current member count
        $currentMemberCount = $this->teamRepository->getMemberCount($teamId);
        if ($maxParticipants < $currentMemberCount) {
            throw new Exception("Cannot reduce max participants below current member count ($currentMemberCount)");
        }

        $team->name = $name;
        $team->description = $description;
        $team->max_participants = $maxParticipants;
        $team->min_participants = $minParticipants;

        $result = $this->teamRepository->update($team);

        // Update team status based on new max
        $this->updateTeamStatusIfFull($teamId);

        return $result;
    }

    /**
     * Request to join a team (creates pending membership)
     */
    public function requestToJoin(string $teamId, int $userId): bool
    {
        $team = $this->teamRepository->findById($teamId);
        if (!$team) {
            throw new Exception("Team not found");
        }

        if ($team->isFull()) {
            throw new Exception("Team is full and not accepting new members");
        }

        if ($team->isInactive()) {
            throw new Exception("Team is inactive and not accepting new members");
        }

        // Check if user is already in team or has pending request
        $existingMembership = $this->teamRepository->getMembership($teamId, $userId);
        if ($existingMembership) {
            if ($existingMembership->isPending()) {
                throw new Exception("You already have a pending request for this team");
            }
            throw new Exception("You are already a member of this team");
        }

        return $this->teamRepository->addMember($teamId, $userId, TeamMembership::STATUS_PENDING);
    }

    /**
     * Approve a membership request (leader only)
     */
    public function approveMembershipRequest(string $teamId, int $leaderId, int $requestUserId): bool
    {
        if (!$this->teamRepository->isUserLeader($teamId, $leaderId)) {
            throw new Exception("Only team leaders can approve membership requests");
        }

        $team = $this->teamRepository->findById($teamId);
        if (!$team) {
            throw new Exception("Team not found");
        }

        $membership = $this->teamRepository->getMembership($teamId, $requestUserId);
        if (!$membership) {
            throw new Exception("Membership request not found");
        }

        if (!$membership->isPending()) {
            throw new Exception("This membership request is not pending");
        }

        // Check if team would be full after approval
        $currentMemberCount = $this->teamRepository->getMemberCount($teamId);
        if ($currentMemberCount >= $team->max_participants) {
            throw new Exception("Team is already full");
        }

        $result = $this->teamRepository->updateMemberStatus($teamId, $requestUserId, TeamMembership::STATUS_MEMBER);

        // Update team status if now full
        $this->updateTeamStatusIfFull($teamId);

        return $result;
    }

    /**
     * Reject a membership request (leader only)
     */
    public function rejectMembershipRequest(string $teamId, int $leaderId, int $requestUserId): bool
    {
        if (!$this->teamRepository->isUserLeader($teamId, $leaderId)) {
            throw new Exception("Only team leaders can reject membership requests");
        }

        $membership = $this->teamRepository->getMembership($teamId, $requestUserId);
        if (!$membership) {
            throw new Exception("Membership request not found");
        }

        if (!$membership->isPending()) {
            throw new Exception("This membership request is not pending");
        }

        // Simply remove the pending request
        return $this->teamRepository->removeMember($teamId, $requestUserId);
    }

    /**
     * Leave a team
     */
    public function leaveTeam(string $teamId, int $userId): bool
    {
        if (!$this->teamRepository->isUserInTeam($teamId, $userId)) {
            throw new Exception("You are not a member of this team");
        }

        $isLeader = $this->teamRepository->isUserLeader($teamId, $userId);
        $result = $this->teamRepository->removeMember($teamId, $userId);

        if ($result) {
            // If the leader left, check if team should be deleted or needs a new leader
            if ($isLeader) {
                $remainingMembers = $this->teamRepository->getMemberCount($teamId);
                if ($remainingMembers === 0) {
                    // Delete team if no members left
                    $this->teamRepository->delete($teamId);
                } else {
                    // Optionally promote another member to leader
                    // For now, team continues without automatic promotion
                }
            }

            // Update team status (may no longer be full)
            $this->updateTeamStatusIfFull($teamId);
        }

        return $result;
    }

    /**
     * Remove a member from team (leader only)
     */
    public function removeMember(string $teamId, int $leaderId, int $memberUserId): bool
    {
        if (!$this->teamRepository->isUserLeader($teamId, $leaderId)) {
            throw new Exception("Only team leaders can remove members");
        }

        if ($leaderId === $memberUserId) {
            throw new Exception("Leaders cannot remove themselves. Use leave team instead.");
        }

        if (!$this->teamRepository->isUserInTeam($teamId, $memberUserId)) {
            throw new Exception("User is not a member of this team");
        }

        $result = $this->teamRepository->removeMember($teamId, $memberUserId);

        if ($result) {
            // Update team status (may no longer be full)
            $this->updateTeamStatusIfFull($teamId);
        }

        return $result;
    }

    /**
     * Promote a member to leader (leader only)
     */
    public function promoteMemberToLeader(string $teamId, int $leaderId, int $memberUserId): bool
    {
        if (!$this->teamRepository->isUserLeader($teamId, $leaderId)) {
            throw new Exception("Only team leaders can promote members");
        }

        if (!$this->teamRepository->isUserInTeam($teamId, $memberUserId)) {
            throw new Exception("User is not a member of this team");
        }

        $membership = $this->teamRepository->getMembership($teamId, $memberUserId);
        if ($membership->isLeader()) {
            throw new Exception("User is already a leader");
        }

        return $this->teamRepository->updateMemberStatus($teamId, $memberUserId, TeamMembership::STATUS_LEAD);
    }

    /**
     * Get all teams
     */
    public function getAllTeams(): array
    {
        return $this->teamRepository->getAll();
    }

    /**
     * Get teams that are searching for members
     */
    public function getSearchingTeams(): array
    {
        return $this->teamRepository->getSearchingTeams();
    }

    /**
     * Get team by ID with members
     */
    public function getTeamWithMembers(string $teamId): ?array
    {
        $team = $this->teamRepository->findById($teamId);
        if (!$team) {
            return null;
        }

        $members = $this->teamRepository->getTeamMembers($teamId);
        $pendingRequests = $this->teamRepository->getPendingRequests($teamId);

        return [
            'team' => $team,
            'members' => $members,
            'pending_requests' => $pendingRequests,
            'member_count' => $this->teamRepository->getMemberCount($teamId),
        ];
    }

    /**
     * Get all teams for a user
     */
    public function getUserTeams(int $userId): array
    {
        return $this->teamRepository->getUserTeams($userId);
    }

    /**
     * Get pending requests for a team (leader only)
     */
    public function getPendingRequests(string $teamId, int $leaderId): array
    {
        if (!$this->teamRepository->isUserLeader($teamId, $leaderId)) {
            throw new Exception("Only team leaders can view pending requests");
        }

        return $this->teamRepository->getPendingRequests($teamId);
    }

    /**
     * Update team status to full if at capacity, or searching if below capacity
     */
    private function updateTeamStatusIfFull(string $teamId): void
    {
        $team = $this->teamRepository->findById($teamId);
        if (!$team || $team->isInactive()) {
            return;
        }

        $memberCount = $this->teamRepository->getMemberCount($teamId);

        if ($memberCount >= $team->max_participants && !$team->isFull()) {
            $this->teamRepository->updateTeamStatus($teamId, Team::STATUS_FULL);
        } elseif ($memberCount < $team->max_participants && $team->isFull()) {
            $this->teamRepository->updateTeamStatus($teamId, Team::STATUS_SEARCHING);
        }
    }

    /**
     * Change team status (leader only)
     */
    public function changeTeamStatus(string $teamId, int $leaderId, string $status): bool
    {
        if (!$this->teamRepository->isUserLeader($teamId, $leaderId)) {
            throw new Exception("Only team leaders can change team status");
        }

        $validStatuses = [Team::STATUS_SEARCHING, Team::STATUS_FULL, Team::STATUS_INACTIVE];
        if (!in_array($status, $validStatuses)) {
            throw new Exception("Invalid team status");
        }

        return $this->teamRepository->updateTeamStatus($teamId, $status);
    }
}
